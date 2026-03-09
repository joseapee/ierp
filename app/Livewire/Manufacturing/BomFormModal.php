<?php

declare(strict_types=1);

namespace App\Livewire\Manufacturing;

use App\Models\Bom;
use App\Models\Product;
use App\Services\BomService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class BomFormModal extends Component
{
    public bool $show = false;

    public ?int $bomId = null;

    public ?int $product_id = null;

    public string $name = '';

    public string $version = '1.0';

    public string $description = '';

    public float $yield_quantity = 1;

    public string $notes = '';

    public array $items = [];

    protected function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'name' => ['required', 'string', 'max:255'],
            'version' => ['required', 'string', 'max:20'],
            'yield_quantity' => ['required', 'numeric', 'gt:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.wastage_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    #[On('openBomFormModal')]
    public function open(?int $bomId = null, ?int $productId = null): void
    {
        $this->resetValidation();
        $this->bomId = $bomId;

        if ($bomId) {
            $bom = Bom::with('items')->findOrFail($bomId);
            $this->product_id = $bom->product_id;
            $this->name = $bom->name;
            $this->version = $bom->version;
            $this->description = $bom->description ?? '';
            $this->yield_quantity = (float) $bom->yield_quantity;
            $this->notes = $bom->notes ?? '';
            $this->items = $bom->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'quantity' => (float) $item->quantity,
                'unit_cost' => (float) $item->unit_cost,
                'wastage_percentage' => (float) $item->wastage_percentage,
            ])->toArray();
        } else {
            $this->reset(['product_id', 'name', 'version', 'description', 'yield_quantity', 'notes', 'items']);
            $this->version = '1.0';
            $this->yield_quantity = 1;
            if ($productId) {
                $this->product_id = $productId;
            }
            $this->addItem();
        }

        $this->show = true;
    }

    public function addItem(): void
    {
        $this->items[] = [
            'product_id' => null,
            'quantity' => 1,
            'unit_cost' => 0,
            'wastage_percentage' => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save(): void
    {
        $this->validate();

        $service = app(BomService::class);

        $data = [
            'product_id' => $this->product_id,
            'name' => $this->name,
            'version' => $this->version,
            'description' => $this->description ?: null,
            'yield_quantity' => $this->yield_quantity,
            'notes' => $this->notes ?: null,
            'items' => $this->items,
        ];

        if ($this->bomId) {
            $bom = Bom::findOrFail($this->bomId);
            $service->update($bom, $data);
        } else {
            $service->create($data);
        }

        $this->show = false;
        $this->dispatch('bomSaved');
        $this->dispatch('toast', message: $this->bomId ? 'BOM updated.' : 'BOM created.', type: 'success');
    }

    public function render(): View
    {
        $manufacturedProducts = Product::where('is_active', true)
            ->where('type', 'manufactured')
            ->orderBy('name')
            ->get();

        $rawMaterials = Product::where('is_active', true)
            ->where('is_purchasable', true)
            ->where('type', '!=', 'manufactured')
            ->orderBy('name')
            ->get();

        return view('livewire.manufacturing.bom-form-modal', [
            'manufacturedProducts' => $manufacturedProducts,
            'rawMaterials' => $rawMaterials,
        ]);
    }
}
