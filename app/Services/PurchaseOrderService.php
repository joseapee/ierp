<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Account;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockBatch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PurchaseOrderService
{
    public function __construct(
        protected StockLedgerService $stockLedgerService,
        protected JournalService $journalService,
    ) {}

    /**
     * Paginated list of purchase orders with optional filters.
     *
     * @param  array{search?: string, status?: string, supplier_id?: int, date_from?: string, date_to?: string}  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return PurchaseOrder::query()
            ->with(['supplier', 'items'])
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where(function ($q) use ($v): void {
                $q->where('order_number', 'like', "%{$v}%")
                    ->orWhereHas('supplier', fn ($q) => $q->where('name', 'like', "%{$v}%"));
            }))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['supplier_id'] ?? null, fn ($q, $v) => $q->where('supplier_id', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->where('order_date', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->where('order_date', '<=', $v))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a new purchase order with items.
     *
     * @param  array{supplier_id: int, order_date: string, expected_date?: string, notes?: string, items: array<int, array{product_id: int, product_variant_id?: int, description?: string, quantity: float, unit_price: float, tax_rate?: float, warehouse_id: int}>}  $data
     */
    public function create(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data): PurchaseOrder {
            $subtotal = 0.0;
            $totalTax = 0.0;

            $itemsData = [];

            foreach ($data['items'] as $item) {
                $quantity = (float) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];
                $taxRate = (float) ($item['tax_rate'] ?? 0);

                $lineSubtotal = $quantity * $unitPrice;
                $lineTax = $lineSubtotal * $taxRate / 100;
                $lineTotal = $lineSubtotal + $lineTax;

                $subtotal += $lineSubtotal;
                $totalTax += $lineTax;

                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'description' => $item['description'] ?? null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $lineTax,
                    'total' => $lineTotal,
                    'quantity_received' => 0,
                    'warehouse_id' => $item['warehouse_id'],
                ];
            }

            $po = PurchaseOrder::create([
                'supplier_id' => $data['supplier_id'],
                'order_number' => $this->generateOrderNumber(),
                'order_date' => $data['order_date'],
                'expected_date' => $data['expected_date'] ?? null,
                'status' => 'draft',
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'total_amount' => $subtotal + $totalTax,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($itemsData as $itemData) {
                $po->items()->create($itemData);
            }

            return $po->load(['supplier', 'items']);
        });
    }

    /**
     * Confirm a draft purchase order.
     */
    public function confirm(PurchaseOrder $po): PurchaseOrder
    {
        if ($po->status !== 'draft') {
            throw new RuntimeException('Only draft purchase orders can be confirmed.');
        }

        $po->update([
            'status' => 'confirmed',
            'confirmed_by' => auth()->id(),
            'confirmed_at' => now(),
        ]);

        return $po;
    }

    /**
     * Receive items against a confirmed or partially received purchase order.
     *
     * @param  array<int, array{purchase_order_item_id: int, quantity_received: float}>  $receivedItems
     */
    public function receiveItems(PurchaseOrder $po, array $receivedItems): PurchaseOrder
    {
        if (! in_array($po->status, ['confirmed', 'partially_received'], true)) {
            throw new RuntimeException('Only confirmed or partially received purchase orders can receive items.');
        }

        return DB::transaction(function () use ($po, $receivedItems): PurchaseOrder {
            $inventoryAccountId = Account::where('code', '1300')
                ->where('tenant_id', $po->tenant_id)
                ->firstOrFail()
                ->id;

            $apAccountId = Account::where('code', '2000')
                ->where('tenant_id', $po->tenant_id)
                ->firstOrFail()
                ->id;

            foreach ($receivedItems as $received) {
                $poItem = PurchaseOrderItem::where('id', $received['purchase_order_item_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $qtyReceived = (float) $received['quantity_received'];

                if ($qtyReceived <= 0) {
                    throw new RuntimeException("Received quantity must be greater than zero for item #{$poItem->id}.");
                }

                $remaining = (float) $poItem->quantity - (float) $poItem->quantity_received;

                if ($qtyReceived > $remaining) {
                    throw new RuntimeException(
                        "Received quantity ({$qtyReceived}) exceeds remaining quantity ({$remaining}) for item #{$poItem->id}."
                    );
                }

                // Create stock batch for received goods.
                $batch = StockBatch::create([
                    'tenant_id' => $po->tenant_id,
                    'product_id' => $poItem->product_id,
                    'product_variant_id' => $poItem->product_variant_id,
                    'warehouse_id' => $poItem->warehouse_id,
                    'initial_quantity' => $qtyReceived,
                    'remaining_quantity' => $qtyReceived,
                    'unit_cost' => $poItem->unit_price,
                    'status' => 'available',
                ]);

                // Record stock ledger entry.
                $this->stockLedgerService->record([
                    'tenant_id' => $po->tenant_id,
                    'product_id' => $poItem->product_id,
                    'product_variant_id' => $poItem->product_variant_id,
                    'warehouse_id' => $poItem->warehouse_id,
                    'stock_batch_id' => $batch->id,
                    'movement_type' => 'purchase_receipt',
                    'quantity' => $qtyReceived,
                    'unit_cost' => (float) $poItem->unit_price,
                    'reference_type' => PurchaseOrder::class,
                    'reference_id' => $po->id,
                ]);

                // Create journal entry for goods receipt.
                $lineTotal = $qtyReceived * (float) $poItem->unit_price;
                $taxAmount = $lineTotal * (float) $poItem->tax_rate / 100;
                $totalAmount = $lineTotal + $taxAmount;

                $this->journalService->createFromSource($po, "Goods receipt: {$po->order_number}", [
                    [
                        'account_id' => $inventoryAccountId,
                        'description' => "Inventory received: {$po->order_number}",
                        'debit' => $totalAmount,
                        'credit' => 0,
                    ],
                    [
                        'account_id' => $apAccountId,
                        'description' => "AP for goods receipt: {$po->order_number}",
                        'debit' => 0,
                        'credit' => $totalAmount,
                    ],
                ], $po->order_number);

                // Update quantity received on the PO item.
                $poItem->increment('quantity_received', $qtyReceived);
            }

            // Determine new PO status based on all items.
            $po->load('items');

            $allReceived = $po->items->every(
                fn (PurchaseOrderItem $item) => (float) $item->quantity_received >= (float) $item->quantity
            );

            if ($allReceived) {
                $po->update([
                    'status' => 'received',
                    'received_by' => auth()->id(),
                    'received_at' => now(),
                ]);
            } else {
                $po->update([
                    'status' => 'partially_received',
                ]);
            }

            return $po->refresh();
        });
    }

    /**
     * Cancel a draft or confirmed purchase order.
     */
    public function cancel(PurchaseOrder $po, string $reason): PurchaseOrder
    {
        if (! in_array($po->status, ['draft', 'confirmed'], true)) {
            throw new RuntimeException('Only draft or confirmed purchase orders can be cancelled.');
        }

        $po->update([
            'status' => 'cancelled',
            'cancelled_by' => auth()->id(),
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        return $po;
    }

    /**
     * Generate a unique order number in PO-XXXXXX format.
     */
    protected function generateOrderNumber(): string
    {
        $last = PurchaseOrder::query()
            ->orderByDesc('id')
            ->value('order_number');

        $nextNum = 1;

        if ($last && preg_match('/PO-(\d+)/', $last, $matches)) {
            $nextNum = (int) $matches[1] + 1;
        }

        return 'PO-'.str_pad((string) $nextNum, 6, '0', STR_PAD_LEFT);
    }
}
