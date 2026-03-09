<?php

declare(strict_types=1);

namespace App\Livewire\Manufacturing;

use App\Models\Bom;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\ProductionService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ProductionOrderForm extends Component
{
    public ?int $product_id = null;

    public ?int $bom_id = null;

    public ?int $warehouse_id = null;

    public float $planned_quantity = 1;

    public string $priority = 'normal';

    public string $planned_start_date = '';

    public string $planned_end_date = '';

    public string $notes = '';

    public array $availableBoms = [];

    public function mount(): void
    {
        $this->planned_start_date = now()->format('Y-m-d');
    }

    public function updatedProductId(): void
    {
        $this->bom_id = null;
        $this->availableBoms = [];

        if ($this->product_id) {
            $this->availableBoms = Bom::where('product_id', $this->product_id)
                ->where('status', 'active')
                ->get()
                ->map(fn ($bom) => ['id' => $bom->id, 'name' => $bom->name.' (v'.$bom->version.')'])
                ->toArray();

            if (count($this->availableBoms) === 1) {
                $this->bom_id = $this->availableBoms[0]['id'];
            }
        }
    }

    public function save(): void
    {
        $this->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'bom_id' => ['required', 'integer', 'exists:boms,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'planned_quantity' => ['required', 'numeric', 'gt:0'],
            'priority' => ['required', 'string', 'in:low,normal,high,urgent'],
        ]);

        $service = app(ProductionService::class);

        $service->createOrder([
            'order_number' => $service->generateOrderNumber(),
            'product_id' => $this->product_id,
            'bom_id' => $this->bom_id,
            'warehouse_id' => $this->warehouse_id,
            'planned_quantity' => $this->planned_quantity,
            'priority' => $this->priority,
            'planned_start_date' => $this->planned_start_date ?: null,
            'planned_end_date' => $this->planned_end_date ?: null,
            'notes' => $this->notes ?: null,
            'created_by' => auth()->id(),
        ]);

        $this->dispatch('toast', message: 'Production order created.', type: 'success');
        $this->redirect(route('manufacturing.orders.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.manufacturing.production-order-form', [
            'products' => Product::where('is_active', true)
                ->where('type', 'manufactured')
                ->orderBy('name')
                ->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
