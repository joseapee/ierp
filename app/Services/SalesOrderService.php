<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Account;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\StockBatch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SalesOrderService
{
    public function __construct(
        protected StockLedgerService $stockLedgerService,
        protected JournalService $journalService,
    ) {}

    /**
     * Paginated list of sales orders with optional filters.
     *
     * @param  array{search?: string, status?: string, customer_id?: int, date_from?: string, date_to?: string}  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return SalesOrder::query()
            ->with(['customer', 'items'])
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where(function ($q) use ($v): void {
                $q->where('order_number', 'like', "%{$v}%")
                    ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$v}%"));
            }))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['customer_id'] ?? null, fn ($q, $v) => $q->where('customer_id', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->where('order_date', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->where('order_date', '<=', $v))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a new sales order with items.
     *
     * @param  array{customer_id: int, order_date: string, due_date?: string, notes?: string, items: array<int, array{product_id: int, product_variant_id?: int, description?: string, quantity: float, unit_price: float, discount_percent?: float, tax_rate?: float, warehouse_id: int}>}  $data
     */
    public function create(array $data): SalesOrder
    {
        return DB::transaction(function () use ($data): SalesOrder {
            $subtotal = 0.0;
            $totalTax = 0.0;
            $totalDiscount = 0.0;

            $itemsData = [];

            foreach ($data['items'] as $item) {
                $quantity = (float) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];
                $discountPercent = (float) ($item['discount_percent'] ?? 0);
                $taxRate = (float) ($item['tax_rate'] ?? 0);

                $lineGross = $quantity * $unitPrice;
                $lineDiscount = $lineGross * $discountPercent / 100;
                $lineSubtotal = $lineGross - $lineDiscount;
                $lineTax = $lineSubtotal * $taxRate / 100;
                $lineTotal = $lineSubtotal + $lineTax;

                $subtotal += $lineSubtotal;
                $totalTax += $lineTax;
                $totalDiscount += $lineDiscount;

                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'description' => $item['description'] ?? null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_percent' => $discountPercent,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $lineTax,
                    'total' => $lineTotal,
                    'quantity_fulfilled' => 0,
                    'warehouse_id' => $item['warehouse_id'],
                ];
            }

            $so = SalesOrder::create([
                'customer_id' => $data['customer_id'],
                'order_number' => $this->generateOrderNumber(),
                'order_date' => $data['order_date'],
                'due_date' => $data['due_date'] ?? null,
                'status' => 'draft',
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'discount_amount' => $totalDiscount,
                'total_amount' => $subtotal + $totalTax,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($itemsData as $itemData) {
                $so->items()->create($itemData);
            }

            return $so->load(['customer', 'items']);
        });
    }

    /**
     * Confirm a draft sales order. Validates stock availability.
     */
    public function confirm(SalesOrder $so): SalesOrder
    {
        if ($so->status !== 'draft') {
            throw new RuntimeException('Only draft sales orders can be confirmed.');
        }

        $so->loadMissing('items');

        foreach ($so->items as $item) {
            $balance = $this->stockLedgerService->getBalance(
                $item->product_id,
                $item->product_variant_id,
                $item->warehouse_id,
            );

            if ($balance < (float) $item->quantity) {
                throw new RuntimeException(
                    "Insufficient stock for product #{$item->product_id} in warehouse #{$item->warehouse_id}. "
                    ."Available: {$balance}, Required: {$item->quantity}."
                );
            }
        }

        $so->update([
            'status' => 'confirmed',
            'confirmed_by' => auth()->id(),
            'confirmed_at' => now(),
        ]);

        return $so;
    }

    /**
     * Fulfill items against a confirmed or partially fulfilled sales order.
     *
     * Uses FIFO batch deduction and creates revenue + COGS journal entries.
     *
     * @param  array<int, array{sales_order_item_id: int, quantity_fulfilled: float}>  $fulfilledItems
     */
    public function fulfillItems(SalesOrder $so, array $fulfilledItems): SalesOrder
    {
        if (! in_array($so->status, ['confirmed', 'partially_fulfilled'], true)) {
            throw new RuntimeException('Only confirmed or partially fulfilled sales orders can be fulfilled.');
        }

        return DB::transaction(function () use ($so, $fulfilledItems): SalesOrder {
            $arAccountId = Account::where('code', '1200')
                ->where('tenant_id', $so->tenant_id)
                ->firstOrFail()
                ->id;

            $revenueAccountId = Account::where('code', '4000')
                ->where('tenant_id', $so->tenant_id)
                ->firstOrFail()
                ->id;

            $vatAccountId = Account::where('code', '2100')
                ->where('tenant_id', $so->tenant_id)
                ->firstOrFail()
                ->id;

            $cogsAccountId = Account::where('code', '5000')
                ->where('tenant_id', $so->tenant_id)
                ->firstOrFail()
                ->id;

            $inventoryAccountId = Account::where('code', '1300')
                ->where('tenant_id', $so->tenant_id)
                ->firstOrFail()
                ->id;

            foreach ($fulfilledItems as $fulfilled) {
                $soItem = SalesOrderItem::where('id', $fulfilled['sales_order_item_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $qtyFulfilled = (float) $fulfilled['quantity_fulfilled'];

                if ($qtyFulfilled <= 0) {
                    throw new RuntimeException("Fulfilled quantity must be greater than zero for item #{$soItem->id}.");
                }

                $remaining = (float) $soItem->quantity - (float) $soItem->quantity_fulfilled;

                if ($qtyFulfilled > $remaining) {
                    throw new RuntimeException(
                        "Fulfilled quantity ({$qtyFulfilled}) exceeds remaining quantity ({$remaining}) for item #{$soItem->id}."
                    );
                }

                // FIFO batch deduction — track weighted cost for COGS.
                $totalCost = 0.0;
                $qtyToDeduct = $qtyFulfilled;

                $batches = StockBatch::where('product_id', $soItem->product_id)
                    ->where('warehouse_id', $soItem->warehouse_id)
                    ->when($soItem->product_variant_id, fn ($q, $v) => $q->where('product_variant_id', $v))
                    ->where('status', 'available')
                    ->where('remaining_quantity', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->lockForUpdate()
                    ->get();

                foreach ($batches as $batch) {
                    if ($qtyToDeduct <= 0) {
                        break;
                    }

                    $deductFromBatch = min($qtyToDeduct, (float) $batch->remaining_quantity);
                    $totalCost += $deductFromBatch * (float) $batch->unit_cost;

                    $batch->update([
                        'remaining_quantity' => (float) $batch->remaining_quantity - $deductFromBatch,
                    ]);

                    $qtyToDeduct -= $deductFromBatch;
                }

                if ($qtyToDeduct > 0) {
                    throw new RuntimeException(
                        "Insufficient stock batches for product #{$soItem->product_id} in warehouse #{$soItem->warehouse_id}."
                    );
                }

                // Record stock ledger entry (negative quantity for sales issue).
                $this->stockLedgerService->record([
                    'tenant_id' => $so->tenant_id,
                    'product_id' => $soItem->product_id,
                    'product_variant_id' => $soItem->product_variant_id,
                    'warehouse_id' => $soItem->warehouse_id,
                    'movement_type' => 'sales_issue',
                    'quantity' => -$qtyFulfilled,
                    'unit_cost' => $qtyFulfilled > 0 ? $totalCost / $qtyFulfilled : 0,
                    'reference_type' => SalesOrder::class,
                    'reference_id' => $so->id,
                ]);

                // Revenue journal entry: Debit AR, Credit Revenue + Credit VAT.
                $lineSubtotal = $qtyFulfilled * (float) $soItem->unit_price * (1 - (float) $soItem->discount_percent / 100);
                $lineTax = $lineSubtotal * (float) $soItem->tax_rate / 100;
                $lineRevenue = $lineSubtotal;

                $revenueLines = [
                    [
                        'account_id' => $arAccountId,
                        'description' => "AR for sales: {$so->order_number}",
                        'debit' => $lineRevenue + $lineTax,
                        'credit' => 0,
                    ],
                    [
                        'account_id' => $revenueAccountId,
                        'description' => "Sales revenue: {$so->order_number}",
                        'debit' => 0,
                        'credit' => $lineRevenue,
                    ],
                ];

                if ($lineTax > 0) {
                    $revenueLines[] = [
                        'account_id' => $vatAccountId,
                        'description' => "VAT on sales: {$so->order_number}",
                        'debit' => 0,
                        'credit' => $lineTax,
                    ];
                }

                $this->journalService->createFromSource(
                    $so,
                    "Sales revenue: {$so->order_number}",
                    $revenueLines,
                    $so->order_number,
                );

                // COGS journal entry: Debit COGS, Credit Inventory at actual FIFO cost.
                if ($totalCost > 0) {
                    $this->journalService->createFromSource($so, "COGS: {$so->order_number}", [
                        [
                            'account_id' => $cogsAccountId,
                            'description' => "Cost of goods sold: {$so->order_number}",
                            'debit' => $totalCost,
                            'credit' => 0,
                        ],
                        [
                            'account_id' => $inventoryAccountId,
                            'description' => "Inventory reduction: {$so->order_number}",
                            'debit' => 0,
                            'credit' => $totalCost,
                        ],
                    ], $so->order_number);
                }

                // Increment quantity fulfilled on the SO item.
                $soItem->increment('quantity_fulfilled', $qtyFulfilled);
            }

            // Determine new SO status.
            $so->load('items');

            $allFulfilled = $so->items->every(
                fn (SalesOrderItem $item) => (float) $item->quantity_fulfilled >= (float) $item->quantity
            );

            if ($allFulfilled) {
                $so->update([
                    'status' => 'fulfilled',
                    'fulfilled_by' => auth()->id(),
                    'fulfilled_at' => now(),
                ]);
            } else {
                $so->update([
                    'status' => 'partially_fulfilled',
                ]);
            }

            return $so->refresh();
        });
    }

    /**
     * Cancel a draft or confirmed sales order.
     */
    public function cancel(SalesOrder $so, string $reason): SalesOrder
    {
        if (! in_array($so->status, ['draft', 'confirmed'], true)) {
            throw new RuntimeException('Only draft or confirmed sales orders can be cancelled.');
        }

        $so->update([
            'status' => 'cancelled',
            'cancelled_by' => auth()->id(),
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        return $so;
    }

    /**
     * Generate a unique order number in SO-XXXXXX format.
     */
    public function generateOrderNumber(): string
    {
        $last = SalesOrder::query()
            ->orderByDesc('id')
            ->value('order_number');

        $nextNum = 1;

        if ($last && preg_match('/SO-(\d+)/', $last, $matches)) {
            $nextNum = (int) $matches[1] + 1;
        }

        return 'SO-'.str_pad((string) $nextNum, 6, '0', STR_PAD_LEFT);
    }
}
