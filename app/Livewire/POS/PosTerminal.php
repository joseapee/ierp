<?php

declare(strict_types=1);

namespace App\Livewire\POS;

use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Services\SalesOrderService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PosTerminal extends Component
{
    public string $search = '';
    public array $cart = [];
    public ?int $selectedProductId = null;
    public ?int $customerId = null;
    public string $paymentType = 'cash';
    public float $amountPaid = 0;
    public bool $showProductModal = false;
    public bool $showPaymentModal = false;
    public bool $showReceiptModal = false;
    public ?int $warehouseId = null;
    public ?int $lastOrderId = null;

    public function updatedSearch(): void
    {
        // Optionally, implement live search debounce
    }

    public function selectProduct(int $productId): void
    {
        $this->selectedProductId = $productId;
        $this->showProductModal = true;
    }

    public function addToCart(int $productId, float $quantity = 1): void
    {
        $product = Product::findOrFail($productId);
        $key = array_search($productId, array_column($this->cart, 'product_id'));
        if ($key !== false) {
            $this->cart[$key]['quantity'] += $quantity;
        } else {
            $this->cart[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'unit_price' => $product->sell_price,
                'quantity' => $quantity,
                'discount_percent' => 0,
                'tax_rate' => $product->tax_rate,
            ];
        }
        $this->showProductModal = false;
    }

    public function removeFromCart(int $index): void
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    public function updateCartQuantity(int $index, float $quantity): void
    {
        if ($quantity > 0) {
            $this->cart[$index]['quantity'] = $quantity;
        }
    }

    public function getSubtotalProperty(): float
    {
        return collect($this->cart)->sum(fn ($item) => $item['quantity'] * $item['unit_price']);
    }

    public function getTaxTotalProperty(): float
    {
        return collect($this->cart)->sum(fn ($item) => ($item['quantity'] * $item['unit_price'] - ($item['quantity'] * $item['unit_price'] * $item['discount_percent'] / 100)) * $item['tax_rate'] / 100);
    }

    public function getGrandTotalProperty(): float
    {
        return $this->subtotal + $this->taxTotal;
    }

    public function openPaymentModal(): void
    {
        $this->showPaymentModal = true;
    }

    public function processPayment(): void
    {
        $this->validate([
            'cart' => 'required|array|min:1',
            'amountPaid' => 'required|numeric|min:' . $this->grandTotal,
        ]);
        $service = app(SalesOrderService::class);
        $order = $service->create([
            'customer_id' => $this->customerId ?? 1, // Default walk-in customer
            'order_date' => now()->toDateString(),
            'items' => collect($this->cart)->map(fn ($item) => [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount_percent' => $item['discount_percent'],
                'tax_rate' => $item['tax_rate'],
                'warehouse_id' => $this->warehouseId ?? 1,
            ])->all(),
        ]);
        $this->lastOrderId = $order->id;
        $this->showPaymentModal = false;
        $this->showReceiptModal = true;
        $this->cart = [];
        $this->amountPaid = 0;
    }

    public function closeReceiptModal(): void
    {
        $this->showReceiptModal = false;
    }

    public function render(): View
    {
        $products = Product::query()
            ->where('is_active', true)
            ->where('is_sellable', true)
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->limit(20)
            ->get();
        $customers = Customer::query()->active()->orderBy('name')->get();
        $warehouses = Warehouse::query()->orderBy('name')->get();
        $order = $this->lastOrderId ? SalesOrder::find($this->lastOrderId) : null;
        return view('livewire.pos.terminal', [
            'products' => $products,
            'customers' => $customers,
            'warehouses' => $warehouses,
            'order' => $order,
        ]);
    }
}
