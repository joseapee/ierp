<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\StockBatch;
use App\Models\StockLedger;
use App\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StockLedgerService
{
    /**
     * Create an immutable ledger entry.
     *
     * The running_balance is calculated from the latest entry for the same
     * product + variant + warehouse combination. A row-level lock prevents
     * concurrent inserts from producing inconsistent balances.
     *
     * @param  array{tenant_id: int, product_id: int, product_variant_id?: int|null, warehouse_id: int, stock_batch_id?: int|null, movement_type: string, quantity: float, unit_cost: float, reference_type?: string|null, reference_id?: int|null, notes?: string|null, created_by?: int|null}  $data
     */
    public function record(array $data): StockLedger
    {
        return DB::transaction(function () use ($data): StockLedger {
            $previousBalance = StockLedger::query()
                ->where('product_id', $data['product_id'])
                ->where('product_variant_id', $data['product_variant_id'] ?? null)
                ->where('warehouse_id', $data['warehouse_id'])
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->value('running_balance') ?? 0.0;

            $quantity = (float) $data['quantity'];
            $unitCost = (float) $data['unit_cost'];

            return StockLedger::create([
                'tenant_id' => $data['tenant_id'],
                'product_id' => $data['product_id'],
                'product_variant_id' => $data['product_variant_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'],
                'stock_batch_id' => $data['stock_batch_id'] ?? null,
                'movement_type' => $data['movement_type'],
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $quantity * $unitCost,
                'running_balance' => (float) $previousBalance + $quantity,
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $data['created_by'] ?? null,
            ]);
        });
    }

    /**
     * Get current stock balance for a product (optionally variant) in a warehouse.
     *
     * Returns 0.0 when no ledger entry exists for the given combination.
     */
    public function getBalance(int $productId, ?int $variantId, int $warehouseId): float
    {
        $balance = StockLedger::query()
            ->where('product_id', $productId)
            ->where('product_variant_id', $variantId)
            ->where('warehouse_id', $warehouseId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->value('running_balance');

        return $balance !== null ? (float) $balance : 0.0;
    }

    /**
     * Get stock levels per warehouse for a product.
     *
     * @return array<int, array{warehouse_id: int, warehouse_name: string, balance: float}>
     */
    public function getStockSummary(int $productId): array
    {
        // Get the latest ledger entry id per warehouse for this product.
        $latestIds = StockLedger::query()
            ->select(DB::raw('MAX(id) as id'))
            ->where('product_id', $productId)
            ->groupBy('warehouse_id')
            ->pluck('id');

        return StockLedger::query()
            ->whereIn('stock_ledger.id', $latestIds)
            ->join('warehouses', 'warehouses.id', '=', 'stock_ledger.warehouse_id')
            ->select('stock_ledger.warehouse_id', 'warehouses.name as warehouse_name', 'stock_ledger.running_balance as balance')
            ->get()
            ->map(fn ($row) => [
                'warehouse_id' => (int) $row->warehouse_id,
                'warehouse_name' => $row->warehouse_name,
                'balance' => (float) $row->balance,
            ])
            ->all();
    }

    /**
     * Calculate inventory valuation for a product.
     *
     * Supported methods:
     *  - weighted_average: sum of remaining_quantity * unit_cost across available batches.
     *  - fifo: same sum but batches ordered by created_at ASC.
     *  - lifo: same sum but batches ordered by created_at DESC.
     *  - standard: product cost_price * total remaining quantity.
     */
    public function getValuation(int $productId, string $method = 'weighted_average'): float
    {
        if ($method === 'standard') {
            $product = Product::findOrFail($productId);

            $totalRemaining = StockBatch::query()
                ->where('product_id', $productId)
                ->where('status', 'available')
                ->sum('remaining_quantity');

            return (float) $product->cost_price * (float) $totalRemaining;
        }

        $query = StockBatch::query()
            ->where('product_id', $productId)
            ->where('status', 'available');

        $query = match ($method) {
            'fifo' => $query->orderBy('created_at', 'asc'),
            'lifo' => $query->orderBy('created_at', 'desc'),
            default => $query, // weighted_average — order is irrelevant
        };

        return (float) $query->get()->sum(
            fn (StockBatch $batch) => (float) $batch->remaining_quantity * (float) $batch->unit_cost
        );
    }

    /**
     * Paginated ledger history with optional filters.
     *
     * @param  array{product_id?: int, product_variant_id?: int, warehouse_id?: int, movement_type?: string, date_from?: string, date_to?: string}  $filters
     */
    public function history(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return StockLedger::query()
            ->with(['product', 'warehouse', 'createdBy'])
            ->when($filters['product_id'] ?? null, fn ($q, $v) => $q->where('product_id', $v))
            ->when($filters['product_variant_id'] ?? null, fn ($q, $v) => $q->where('product_variant_id', $v))
            ->when($filters['warehouse_id'] ?? null, fn ($q, $v) => $q->where('warehouse_id', $v))
            ->when($filters['movement_type'] ?? null, fn ($q, $v) => $q->where('movement_type', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->where('created_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->where('created_at', '<=', $v))
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}
