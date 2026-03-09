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
class StockAdjustmentForm extends Component
{
    public ?int $warehouse_id = null;

    public string $adjustment_number = '';

    public string $reason = '';

    public string $notes = '';

    public array $items = [];

    public function mount(): void
    {
        $this->adjustment_number = 'ADJ-'.now()->format('YmdHis');
        $this->addItem();
    }

    public function addItem(): void
    {
        $this->items[] = [
            'product_id' => null,
            'product_variant_id' => null,
            'stock_batch_id' => null,
            'type' => 'addition',
            'quantity' => 0,
            'unit_cost' => 0,
            'reason' => '',
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
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'adjustment_number' => ['required', 'string', 'max:50'],
            'reason' => ['required', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.type' => ['required', 'string', 'in:addition,subtraction'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        app(StockMovementService::class)->createAdjustment([
            'warehouse_id' => $this->warehouse_id,
            'adjustment_number' => $this->adjustment_number,
            'reason' => $this->reason,
            'notes' => $this->notes,
            'adjusted_by' => auth()->id(),
            'items' => $this->items,
        ]);

        $this->dispatch('toast', message: 'Adjustment created successfully.', type: 'success');
        $this->redirect(route('stock.adjustments.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.stock.stock-adjustment-form', [
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
