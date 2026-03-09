<?php

declare(strict_types=1);

namespace App\Livewire\Manufacturing;

use App\Models\ProductionOrder;
use App\Models\ProductionStage;
use App\Models\StockBatch;
use App\Models\Warehouse;
use App\Services\ProductionService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ProductionOrderDetail extends Component
{
    public ProductionOrder $order;

    // Material consumption form
    public bool $showConsumeModal = false;

    public ?int $consume_product_id = null;

    public ?int $consume_warehouse_id = null;

    public ?int $consume_batch_id = null;

    public float $consume_planned_qty = 0;

    public float $consume_actual_qty = 0;

    public float $consume_unit_cost = 0;

    public float $consume_wastage_qty = 0;

    public string $consume_notes = '';

    // Task form
    public bool $showTaskModal = false;

    public string $task_name = '';

    public ?int $task_stage_id = null;

    public string $task_notes = '';

    // Completion form
    public bool $showCompleteModal = false;

    public float $completed_quantity = 0;

    public float $rejected_quantity = 0;

    public function mount(ProductionOrder $order): void
    {
        $this->order = $order->load([
            'product', 'bom.items.product', 'warehouse',
            'tasks.currentStage', 'tasks.assignedUser',
            'materialConsumptions.product', 'wipInventory',
            'createdByUser', 'completedByUser',
        ]);
        $this->completed_quantity = (float) $order->planned_quantity;
    }

    public function confirmOrder(): void
    {
        app(ProductionService::class)->confirmOrder($this->order);
        $this->order = $this->order->fresh()->load([
            'product', 'bom.items.product', 'warehouse',
            'tasks.currentStage', 'materialConsumptions.product', 'wipInventory',
        ]);
        $this->dispatch('toast', message: 'Order confirmed.', type: 'success');
    }

    public function startProduction(): void
    {
        app(ProductionService::class)->startProduction($this->order);
        $this->order = $this->order->fresh()->load([
            'product', 'bom.items.product', 'warehouse',
            'tasks.currentStage', 'materialConsumptions.product', 'wipInventory',
        ]);
        $this->dispatch('toast', message: 'Production started.', type: 'success');
    }

    public function cancelOrder(): void
    {
        app(ProductionService::class)->cancelOrder($this->order);
        $this->order = $this->order->fresh()->load([
            'product', 'bom.items.product', 'warehouse',
            'tasks.currentStage', 'materialConsumptions.product', 'wipInventory',
        ]);
        $this->dispatch('toast', message: 'Order cancelled.', type: 'warning');
    }

    public function openConsumeModal(int $productId, float $plannedQty, float $unitCost): void
    {
        $this->consume_product_id = $productId;
        $this->consume_warehouse_id = $this->order->warehouse_id;
        $this->consume_batch_id = null;
        $this->consume_planned_qty = $plannedQty;
        $this->consume_actual_qty = $plannedQty;
        $this->consume_unit_cost = $unitCost;
        $this->consume_wastage_qty = 0;
        $this->consume_notes = '';
        $this->showConsumeModal = true;
    }

    public function consumeMaterial(): void
    {
        $this->validate([
            'consume_product_id' => ['required', 'integer'],
            'consume_warehouse_id' => ['required', 'integer'],
            'consume_actual_qty' => ['required', 'numeric', 'gt:0'],
            'consume_unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        app(ProductionService::class)->consumeMaterial($this->order, [
            'product_id' => $this->consume_product_id,
            'warehouse_id' => $this->consume_warehouse_id,
            'stock_batch_id' => $this->consume_batch_id,
            'planned_quantity' => $this->consume_planned_qty,
            'actual_quantity' => $this->consume_actual_qty,
            'unit_cost' => $this->consume_unit_cost,
            'wastage_quantity' => $this->consume_wastage_qty,
            'consumed_by' => auth()->id(),
            'notes' => $this->consume_notes ?: null,
        ]);

        $this->showConsumeModal = false;
        $this->refreshOrder();
        $this->dispatch('toast', message: 'Material consumed.', type: 'success');
    }

    public function openTaskModal(): void
    {
        $this->task_name = '';
        $this->task_stage_id = null;
        $this->task_notes = '';
        $this->showTaskModal = true;
    }

    public function createTask(): void
    {
        $this->validate([
            'task_name' => ['required', 'string', 'max:255'],
        ]);

        app(ProductionService::class)->createTask($this->order, [
            'name' => $this->task_name,
            'current_stage_id' => $this->task_stage_id,
            'notes' => $this->task_notes ?: null,
        ]);

        $this->showTaskModal = false;
        $this->refreshOrder();
        $this->dispatch('toast', message: 'Task created.', type: 'success');
    }

    public function startTask(int $taskId): void
    {
        $task = $this->order->tasks()->findOrFail($taskId);
        app(ProductionService::class)->startTask($task);
        $this->refreshOrder();
    }

    public function completeTask(int $taskId): void
    {
        $task = $this->order->tasks()->findOrFail($taskId);
        app(ProductionService::class)->completeTask($task);
        $this->refreshOrder();
    }

    public function openCompleteModal(): void
    {
        $this->completed_quantity = (float) $this->order->planned_quantity;
        $this->rejected_quantity = 0;
        $this->showCompleteModal = true;
    }

    public function completeProduction(): void
    {
        $this->validate([
            'completed_quantity' => ['required', 'numeric', 'gt:0'],
            'rejected_quantity' => ['nullable', 'numeric', 'min:0'],
        ]);

        app(ProductionService::class)->completeProduction(
            $this->order,
            $this->completed_quantity,
            $this->rejected_quantity,
            auth()->id()
        );

        $this->showCompleteModal = false;
        $this->refreshOrder();
        $this->dispatch('toast', message: 'Production completed.', type: 'success');
    }

    private function refreshOrder(): void
    {
        $this->order = $this->order->fresh()->load([
            'product', 'bom.items.product', 'warehouse',
            'tasks.currentStage', 'tasks.assignedUser',
            'materialConsumptions.product', 'wipInventory',
            'createdByUser', 'completedByUser',
        ]);
    }

    public function render(): View
    {
        $stages = ProductionStage::where('is_active', true)->orderBy('sort_order')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        $availableBatches = [];
        if ($this->consume_product_id && $this->consume_warehouse_id) {
            $availableBatches = StockBatch::where('product_id', $this->consume_product_id)
                ->where('warehouse_id', $this->consume_warehouse_id)
                ->where('status', 'available')
                ->where('remaining_quantity', '>', 0)
                ->get();
        }

        return view('livewire.manufacturing.production-order-detail', [
            'stages' => $stages,
            'warehouses' => $warehouses,
            'availableBatches' => $availableBatches,
        ]);
    }
}
