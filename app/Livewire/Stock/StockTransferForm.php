<?php

declare(strict_types=1);

namespace App\Livewire\Stock;

use App\Models\Product;
use App\Models\Warehouse;
use App\Services\StockMovementService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class StockTransferForm extends Component
{
    public string $transfer_number = '';

    public ?int $from_warehouse_id = null;

    public ?int $to_warehouse_id = null;

    public string $notes = '';

    public array $items = [];

    public function mount(): void
    {
        $this->transfer_number = 'TRF-'.now()->format('YmdHis');
        $this->addItem();
    }

    public function addItem(): void
    {
        $this->items[] = [
            'product_id' => null,
            'product_variant_id' => null,
            'stock_batch_id' => null,
            'quantity' => 0,
            'unit_cost' => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save(): void
    {
        $this->validate([
            'transfer_number' => ['required', 'string', 'max:50'],
            'from_warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'to_warehouse_id' => ['required', 'integer', 'exists:warehouses,id', 'different:from_warehouse_id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        app(StockMovementService::class)->createTransfer([
            'transfer_number' => $this->transfer_number,
            'from_warehouse_id' => $this->from_warehouse_id,
            'to_warehouse_id' => $this->to_warehouse_id,
            'notes' => $this->notes,
            'initiated_by' => auth()->id(),
            'items' => $this->items,
        ]);

        $this->dispatch('toast', message: 'Transfer created successfully.', type: 'success');
        $this->redirect(route('stock.transfers.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.stock.stock-transfer-form', [
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'products' => Product::where('is_active', true)->where('is_stockable', true)->orderBy('name')->get(),
        ]);
    }
}
