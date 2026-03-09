<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\StockAdjustment;
use App\Models\StockBatch;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockMovementService
{
    public function __construct(
        private readonly StockLedgerService $stockLedgerService,
    ) {}

    /**
     * Create a stock adjustment in draft status.
     *
     * @param  array{tenant_id: int, warehouse_id: int, adjustment_number: string, reason: string, notes?: string, adjusted_by: int, adjusted_at: string, items: array}  $data
     */
    public function createAdjustment(array $data): StockAdjustment
    {
        return DB::transaction(function () use ($data): StockAdjustment {
            $adjustment = StockAdjustment::create([
                'tenant_id' => $data['tenant_id'],
                'warehouse_id' => $data['warehouse_id'],
                'adjustment_number' => $data['adjustment_number'],
                'reason' => $data['reason'],
                'notes' => $data['notes'] ?? null,
                'status' => 'draft',
                'adjusted_by' => $data['adjusted_by'],
                'adjusted_at' => $data['adjusted_at'],
            ]);

            foreach ($data['items'] as $item) {
                $adjustment->items()->create([
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'stock_batch_id' => $item['stock_batch_id'] ?? null,
                    'type' => $item['type'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'reason' => $item['reason'] ?? null,
                ]);
            }

            return $adjustment->load('items');
        });
    }

    /**
     * Approve a draft stock adjustment and apply inventory changes.
     */
    public function approveAdjustment(StockAdjustment $adjustment, int $approvedBy): void
    {
        DB::transaction(function () use ($adjustment, $approvedBy): void {
            $adjustment->update([
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'approved_at' => now(),
            ]);

            foreach ($adjustment->items as $item) {
                if ($item->type === 'addition') {
                    $this->stockLedgerService->record([
                        'tenant_id' => $adjustment->tenant_id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'warehouse_id' => $adjustment->warehouse_id,
                        'stock_batch_id' => $item->stock_batch_id,
                        'movement_type' => 'adjustment_in',
                        'quantity' => $item->quantity,
                        'unit_cost' => $item->unit_cost,
                        'reference_type' => StockAdjustment::class,
                        'reference_id' => $adjustment->id,
                        'created_by' => $approvedBy,
                    ]);

                    if ($item->stock_batch_id) {
                        $batch = StockBatch::find($item->stock_batch_id);
                        $batch->update([
                            'remaining_quantity' => $batch->remaining_quantity + $item->quantity,
                        ]);
                    } else {
                        StockBatch::create([
                            'tenant_id' => $adjustment->tenant_id,
                            'product_id' => $item->product_id,
                            'product_variant_id' => $item->product_variant_id,
                            'warehouse_id' => $adjustment->warehouse_id,
                            'initial_quantity' => $item->quantity,
                            'remaining_quantity' => $item->quantity,
                            'unit_cost' => $item->unit_cost,
                            'status' => 'available',
                        ]);
                    }
                }

                if ($item->type === 'subtraction') {
                    $this->stockLedgerService->record([
                        'tenant_id' => $adjustment->tenant_id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'warehouse_id' => $adjustment->warehouse_id,
                        'stock_batch_id' => $item->stock_batch_id,
                        'movement_type' => 'adjustment_out',
                        'quantity' => -$item->quantity,
                        'unit_cost' => $item->unit_cost,
                        'reference_type' => StockAdjustment::class,
                        'reference_id' => $adjustment->id,
                        'created_by' => $approvedBy,
                    ]);

                    if ($item->stock_batch_id) {
                        $batch = StockBatch::find($item->stock_batch_id);
                        $batch->update([
                            'remaining_quantity' => $batch->remaining_quantity - $item->quantity,
                        ]);
                    }
                }
            }
        });
    }

    /**
     * Create a stock transfer in draft status.
     *
     * @param  array{tenant_id: int, transfer_number: string, from_warehouse_id: int, to_warehouse_id: int, notes?: string, initiated_by: int, items: array}  $data
     */
    public function createTransfer(array $data): StockTransfer
    {
        return DB::transaction(function () use ($data): StockTransfer {
            $transfer = StockTransfer::create([
                'tenant_id' => $data['tenant_id'],
                'transfer_number' => $data['transfer_number'],
                'from_warehouse_id' => $data['from_warehouse_id'],
                'to_warehouse_id' => $data['to_warehouse_id'],
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
                'initiated_by' => $data['initiated_by'],
            ]);

            foreach ($data['items'] as $item) {
                $transfer->items()->create([
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'stock_batch_id' => $item['stock_batch_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                ]);
            }

            return $transfer->load('items');
        });
    }

    /**
     * Ship a draft transfer — deduct stock from the source warehouse.
     */
    public function shipTransfer(StockTransfer $transfer): void
    {
        DB::transaction(function () use ($transfer): void {
            $transfer->update(['status' => 'in_transit']);

            foreach ($transfer->items as $item) {
                $this->stockLedgerService->record([
                    'tenant_id' => $transfer->tenant_id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'warehouse_id' => $transfer->from_warehouse_id,
                    'stock_batch_id' => $item->stock_batch_id,
                    'movement_type' => 'transfer_out',
                    'quantity' => -$item->quantity,
                    'unit_cost' => $item->unit_cost,
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $transfer->id,
                    'created_by' => $transfer->initiated_by,
                ]);

                if ($item->stock_batch_id) {
                    $batch = StockBatch::find($item->stock_batch_id);
                    $batch->update([
                        'remaining_quantity' => $batch->remaining_quantity - $item->quantity,
                    ]);
                }
            }
        });
    }

    /**
     * Complete an in-transit transfer — receive stock at the destination warehouse.
     */
    public function completeTransfer(StockTransfer $transfer, int $completedBy): void
    {
        DB::transaction(function () use ($transfer, $completedBy): void {
            $transfer->update([
                'status' => 'completed',
                'completed_by' => $completedBy,
                'completed_at' => now(),
            ]);

            foreach ($transfer->items as $item) {
                $this->stockLedgerService->record([
                    'tenant_id' => $transfer->tenant_id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'warehouse_id' => $transfer->to_warehouse_id,
                    'stock_batch_id' => $item->stock_batch_id,
                    'movement_type' => 'transfer_in',
                    'quantity' => $item->quantity,
                    'unit_cost' => $item->unit_cost,
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $transfer->id,
                    'created_by' => $completedBy,
                ]);

                StockBatch::create([
                    'tenant_id' => $transfer->tenant_id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'warehouse_id' => $transfer->to_warehouse_id,
                    'initial_quantity' => $item->quantity,
                    'remaining_quantity' => $item->quantity,
                    'unit_cost' => $item->unit_cost,
                    'status' => 'available',
                ]);
            }
        });
    }

    /**
     * Cancel a draft transfer. Throws if the transfer is already in transit.
     */
    public function cancelTransfer(StockTransfer $transfer): void
    {
        if ($transfer->status === 'in_transit') {
            throw new RuntimeException('Cannot cancel a transfer that is already in transit.');
        }

        if ($transfer->status !== 'draft') {
            throw new RuntimeException('Only draft transfers can be cancelled.');
        }

        $transfer->update(['status' => 'cancelled']);
    }
}
