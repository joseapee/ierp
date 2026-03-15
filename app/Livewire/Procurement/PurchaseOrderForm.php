<?php

declare(strict_types=1);

namespace App\Livewire\Procurement;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\PurchaseOrderService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PurchaseOrderForm extends Component
{
    public string $supplier_id = '';

    public string $order_date = '';

    public string $expected_date = '';

    public string $notes = '';

    /** @var array<int, array{product_id: string, quantity: string, unit_price: string, tax_rate: string, warehouse_id: string}> */
    public array $items = [];

    public function mount(): void
    {
        $this->order_date = now()->toDateString();
        $this->addItem();
    }

    public function addItem(): void
    {
        $this->items[] = [
            'product_id' => '',
            'quantity' => '1',
            'unit_price' => '0',
            'tax_rate' => '0',
            'warehouse_id' => '',
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);

        if (count($this->items) === 0) {
            $this->addItem();
        }
    }

    public function getSubtotalProperty(): float
    {
        return collect($this->items)->sum(fn ($item) => (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0));
    }

    public function getTaxTotalProperty(): float
    {
        return collect($this->items)->sum(function ($item) {
            $lineSubtotal = (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0);

            return $lineSubtotal * (float) ($item['tax_rate'] ?? 0) / 100;
        });
    }

    public function getGrandTotalProperty(): float
    {
        return $this->subtotal + $this->taxTotal;
    }

    public function save(): void
    {
        $this->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $service = app(PurchaseOrderService::class);

        $service->create([
            'supplier_id' => (int) $this->supplier_id,
            'order_date' => $this->order_date,
            'expected_date' => $this->expected_date ?: null,
            'notes' => $this->notes ?: null,
            'items' => collect($this->items)->map(fn ($item) => [
                'product_id' => (int) $item['product_id'],
                'quantity' => (float) $item['quantity'],
                'unit_price' => (float) $item['unit_price'],
                'tax_rate' => (float) ($item['tax_rate'] ?? 0),
                'warehouse_id' => (int) $item['warehouse_id'],
            ])->all(),
        ]);

        $this->dispatch('toast', message: 'Purchase order created successfully.', type: 'success');
        $this->redirect(route('procurement.purchase-orders.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.procurement.purchase-order-form', [
            'suppliers' => Supplier::query()->active()->orderBy('name')->get(),
            'products' => Product::query()->where('is_purchasable', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::query()->orderBy('name')->get(),
        ]);
    }
}
