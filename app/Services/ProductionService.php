<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MaterialConsumption;
use App\Models\ProductionOrder;
use App\Models\ProductionTask;
use App\Models\StockBatch;
use App\Models\WipInventory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProductionService
{
    public function __construct(
        private readonly StockLedgerService $stockLedgerService,
        private readonly BomService $bomService,
        private readonly PricingService $pricingService,
    ) {}

    /**
     * Paginated, filterable list of production orders.
     *
     * @param  array{search?: string, status?: string, priority?: string, product_id?: int}  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return ProductionOrder::query()
            ->with(['product', 'bom', 'warehouse'])
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where(fn ($q) => $q
                ->where('order_number', 'like', "%{$v}%")
                ->orWhereHas('product', fn ($q) => $q->where('name', 'like', "%{$v}%"))
            ))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['priority'] ?? null, fn ($q, $v) => $q->where('priority', $v))
            ->when($filters['product_id'] ?? null, fn ($q, $v) => $q->where('product_id', $v))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a production order in draft status.
     *
     * @param  array{order_number: string, product_id: int, product_variant_id?: int, bom_id: int, warehouse_id: int, planned_quantity: float, priority?: string, planned_start_date?: string, planned_end_date?: string, notes?: string, created_by?: int}  $data
     */
    public function createOrder(array $data): ProductionOrder
    {
        return DB::transaction(function () use ($data): ProductionOrder {
            $order = ProductionOrder::create([
                'order_number' => $data['order_number'],
                'product_id' => $data['product_id'],
                'product_variant_id' => $data['product_variant_id'] ?? null,
                'bom_id' => $data['bom_id'],
                'warehouse_id' => $data['warehouse_id'],
                'planned_quantity' => $data['planned_quantity'],
                'completed_quantity' => 0,
                'rejected_quantity' => 0,
                'unit_cost' => 0,
                'total_cost' => 0,
                'status' => 'draft',
                'priority' => $data['priority'] ?? 'normal',
                'planned_start_date' => $data['planned_start_date'] ?? null,
                'planned_end_date' => $data['planned_end_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $data['created_by'] ?? null,
            ]);

            // Create WIP inventory tracker.
            WipInventory::create([
                'production_order_id' => $order->id,
                'quantity' => $data['planned_quantity'],
                'unit_cost' => 0,
                'total_cost' => 0,
                'status' => 'in_progress',
                'last_updated_at' => now(),
            ]);

            return $order->load(['product', 'bom', 'warehouse', 'wipInventory']);
        });
    }

    /**
     * Confirm a draft production order.
     */
    public function confirmOrder(ProductionOrder $order): ProductionOrder
    {
        if ($order->status !== 'draft') {
            throw new RuntimeException('Only draft orders can be confirmed.');
        }

        $order->update(['status' => 'confirmed']);

        return $order->fresh();
    }

    /**
     * Start production on a confirmed order.
     */
    public function startProduction(ProductionOrder $order): ProductionOrder
    {
        if (! in_array($order->status, ['confirmed'], true)) {
            throw new RuntimeException('Only confirmed orders can be started.');
        }

        $order->update([
            'status' => 'in_progress',
            'actual_start_date' => now(),
        ]);

        return $order->fresh();
    }

    /**
     * Consume materials for a production order (Mode B — deduct during production).
     *
     * @param  array{product_id: int, product_variant_id?: int, warehouse_id: int, stock_batch_id?: int, planned_quantity: float, actual_quantity: float, unit_cost: float, wastage_quantity?: float, consumed_by?: int, notes?: string}  $data
     */
    public function consumeMaterial(ProductionOrder $order, array $data): MaterialConsumption
    {
        if (! in_array($order->status, ['in_progress'], true)) {
            throw new RuntimeException('Materials can only be consumed on in-progress orders.');
        }

        return DB::transaction(function () use ($order, $data): MaterialConsumption {
            $actualQty = (float) $data['actual_quantity'];
            $unitCost = (float) $data['unit_cost'];
            $wastageQty = (float) ($data['wastage_quantity'] ?? 0);

            $consumption = MaterialConsumption::create([
                'production_order_id' => $order->id,
                'product_id' => $data['product_id'],
                'product_variant_id' => $data['product_variant_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'],
                'stock_batch_id' => $data['stock_batch_id'] ?? null,
                'planned_quantity' => $data['planned_quantity'],
                'actual_quantity' => $actualQty,
                'unit_cost' => $unitCost,
                'total_cost' => $actualQty * $unitCost,
                'wastage_quantity' => $wastageQty,
                'consumed_at' => now(),
                'consumed_by' => $data['consumed_by'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Record ledger entry for material issue.
            $this->stockLedgerService->record([
                'tenant_id' => $order->tenant_id,
                'product_id' => $data['product_id'],
                'product_variant_id' => $data['product_variant_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'],
                'stock_batch_id' => $data['stock_batch_id'] ?? null,
                'movement_type' => 'production_issue',
                'quantity' => -$actualQty,
                'unit_cost' => $unitCost,
                'reference_type' => ProductionOrder::class,
                'reference_id' => $order->id,
                'created_by' => $data['consumed_by'] ?? null,
            ]);

            // Reduce batch remaining quantity if a specific batch was used.
            if (! empty($data['stock_batch_id'])) {
                $batch = StockBatch::find($data['stock_batch_id']);

                if ($batch) {
                    $batch->update([
                        'remaining_quantity' => $batch->remaining_quantity - $actualQty,
                    ]);
                }
            }

            // Update WIP cost.
            $this->updateWipCost($order);

            return $consumption;
        });
    }

    /**
     * Create a production task and assign it to a stage.
     *
     * @param  array{current_stage_id?: int, task_number?: string, name: string, description?: string, sort_order?: int, estimated_duration_minutes?: int, assigned_to?: int, notes?: string}  $data
     */
    public function createTask(ProductionOrder $order, array $data): ProductionTask
    {
        return ProductionTask::create([
            'production_order_id' => $order->id,
            'current_stage_id' => $data['current_stage_id'] ?? null,
            'task_number' => $data['task_number'] ?? null,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => 'pending',
            'sort_order' => $data['sort_order'] ?? 0,
            'estimated_duration_minutes' => $data['estimated_duration_minutes'] ?? null,
            'assigned_to' => $data['assigned_to'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Move a task to a new stage.
     */
    public function moveTask(ProductionTask $task, int $stageId): ProductionTask
    {
        $task->update(['current_stage_id' => $stageId]);

        return $task->fresh()->load('currentStage');
    }

    /**
     * Start a task.
     */
    public function startTask(ProductionTask $task): ProductionTask
    {
        if ($task->status !== 'pending') {
            throw new RuntimeException('Only pending tasks can be started.');
        }

        $task->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return $task->fresh();
    }

    /**
     * Complete a task.
     */
    public function completeTask(ProductionTask $task): ProductionTask
    {
        if ($task->status !== 'in_progress') {
            throw new RuntimeException('Only in-progress tasks can be completed.');
        }

        $durationMinutes = null;

        if ($task->started_at) {
            $durationMinutes = (int) $task->started_at->diffInMinutes(now());
        }

        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
            'actual_duration_minutes' => $durationMinutes,
        ]);

        return $task->fresh();
    }

    /**
     * Complete a production order — create finished goods inventory.
     */
    public function completeProduction(ProductionOrder $order, float $completedQuantity, ?float $rejectedQuantity = null, ?int $completedBy = null): ProductionOrder
    {
        if ($order->status !== 'in_progress') {
            throw new RuntimeException('Only in-progress orders can be completed.');
        }

        return DB::transaction(function () use ($order, $completedQuantity, $rejectedQuantity, $completedBy): ProductionOrder {
            $rejected = $rejectedQuantity ?? 0;

            // Calculate unit cost from total consumed materials.
            $totalMaterialCost = (float) $order->materialConsumptions()->sum('total_cost');
            $unitCost = $completedQuantity > 0 ? $totalMaterialCost / $completedQuantity : 0;

            $order->update([
                'status' => 'completed',
                'completed_quantity' => $completedQuantity,
                'rejected_quantity' => $rejected,
                'unit_cost' => $unitCost,
                'total_cost' => $totalMaterialCost,
                'actual_end_date' => now(),
                'completed_by' => $completedBy,
            ]);

            // Record finished goods receipt in the ledger.
            $this->stockLedgerService->record([
                'tenant_id' => $order->tenant_id,
                'product_id' => $order->product_id,
                'product_variant_id' => $order->product_variant_id,
                'warehouse_id' => $order->warehouse_id,
                'movement_type' => 'production_receipt',
                'quantity' => $completedQuantity,
                'unit_cost' => $unitCost,
                'reference_type' => ProductionOrder::class,
                'reference_id' => $order->id,
                'created_by' => $completedBy,
            ]);

            // Create a stock batch for the finished goods.
            StockBatch::create([
                'tenant_id' => $order->tenant_id,
                'product_id' => $order->product_id,
                'product_variant_id' => $order->product_variant_id,
                'warehouse_id' => $order->warehouse_id,
                'initial_quantity' => $completedQuantity,
                'remaining_quantity' => $completedQuantity,
                'unit_cost' => $unitCost,
                'status' => 'available',
                'manufacturing_date' => now(),
            ]);

            // Update WIP inventory to completed.
            $wip = $order->wipInventory;

            if ($wip) {
                $wip->update([
                    'status' => 'completed',
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalMaterialCost,
                    'last_updated_at' => now(),
                ]);
            }

            // Update product cost_price and auto-recalculate sell_price.
            $product = $order->product;

            if ($product->type === 'manufactured') {
                $this->pricingService->recalculateFromBom($product);
            }

            return $order->fresh()->load(['product', 'bom', 'warehouse', 'wipInventory']);
        });
    }

    /**
     * Cancel a production order. Only draft or confirmed orders can be cancelled.
     */
    public function cancelOrder(ProductionOrder $order): ProductionOrder
    {
        if (! in_array($order->status, ['draft', 'confirmed'], true)) {
            throw new RuntimeException('Only draft or confirmed orders can be cancelled.');
        }

        $order->update(['status' => 'cancelled']);

        $wip = $order->wipInventory;

        if ($wip) {
            $wip->update(['status' => 'scrapped']);
        }

        return $order->fresh();
    }

    /**
     * Get production board data — tasks grouped by stage.
     *
     * @return array<int, array{stage_id: int, stage_name: string, tasks: \Illuminate\Database\Eloquent\Collection}>
     */
    public function getBoardData(int $productionOrderId): array
    {
        $order = ProductionOrder::with(['tasks.currentStage', 'tasks.assignedUser'])
            ->findOrFail($productionOrderId);

        $grouped = $order->tasks->groupBy('current_stage_id');

        $result = [];

        foreach ($grouped as $stageId => $tasks) {
            $stage = $tasks->first()?->currentStage;
            $result[] = [
                'stage_id' => $stageId,
                'stage_name' => $stage?->name ?? 'Unassigned',
                'tasks' => $tasks,
            ];
        }

        return $result;
    }

    /**
     * Generate an order number for a new production order.
     */
    public function generateOrderNumber(): string
    {
        $latest = ProductionOrder::query()
            ->orderByDesc('id')
            ->value('order_number');

        if ($latest && preg_match('/MO-(\d+)/', $latest, $matches)) {
            $next = ((int) $matches[1]) + 1;
        } else {
            $next = 1;
        }

        return 'MO-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Update the WIP cost totals from material consumptions.
     */
    private function updateWipCost(ProductionOrder $order): void
    {
        $totalCost = (float) $order->materialConsumptions()->sum('total_cost');
        $qty = (float) $order->planned_quantity;
        $unitCost = $qty > 0 ? $totalCost / $qty : 0;

        $wip = $order->wipInventory;

        if ($wip) {
            $wip->update([
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'last_updated_at' => now(),
            ]);
        }
    }
}
