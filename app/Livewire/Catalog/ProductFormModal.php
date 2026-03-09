<?php

declare(strict_types=1);

namespace App\Livewire\Catalog;

use App\Http\Requests\Catalog\StoreProductRequest;
use App\Http\Requests\Catalog\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\UnitOfMeasure;
use App\Services\ProductService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;

class ProductFormModal extends Component
{
    public bool $showModal = false;

    public ?int $productId = null;

    public string $name = '';

    public string $slug = '';

    public string $sku = '';

    public string $type = 'standard';

    public ?int $category_id = null;

    public ?int $brand_id = null;

    public ?int $base_unit_id = null;

    public string $description = '';

    public string $short_description = '';

    public string $barcode = '';

    public string $cost_price = '0';

    public string $sell_price = '0';

    public string $tax_rate = '0';

    public string $valuation_method = 'weighted_average';

    public string $reorder_level = '0';

    public string $reorder_quantity = '0';

    public bool $is_active = true;

    public bool $is_purchasable = true;

    public bool $is_sellable = true;

    public bool $is_stockable = true;

    public array $attribute_ids = [];

    protected $listeners = [
        'openProductFormModal' => 'open',
    ];

    public function open(?int $productId = null): void
    {
        $this->resetValidation();
        $this->reset([
            'name', 'slug', 'sku', 'type', 'category_id', 'brand_id', 'base_unit_id',
            'description', 'short_description', 'barcode', 'cost_price', 'sell_price',
            'tax_rate', 'valuation_method', 'reorder_level', 'reorder_quantity',
            'is_active', 'is_purchasable', 'is_sellable', 'is_stockable', 'attribute_ids',
        ]);
        $this->productId = $productId;
        $this->is_active = true;
        $this->is_purchasable = true;
        $this->is_sellable = true;
        $this->is_stockable = true;
        $this->type = 'standard';
        $this->valuation_method = 'weighted_average';
        $this->cost_price = '0';
        $this->sell_price = '0';
        $this->tax_rate = '0';
        $this->reorder_level = '0';
        $this->reorder_quantity = '0';

        if ($productId) {
            $product = Product::with('attributes')->findOrFail($productId);
            $this->name = $product->name;
            $this->slug = $product->slug;
            $this->sku = $product->sku;
            $this->type = $product->type;
            $this->category_id = $product->category_id;
            $this->brand_id = $product->brand_id;
            $this->base_unit_id = $product->base_unit_id;
            $this->description = $product->description ?? '';
            $this->short_description = $product->short_description ?? '';
            $this->barcode = $product->barcode ?? '';
            $this->cost_price = (string) $product->cost_price;
            $this->sell_price = (string) $product->sell_price;
            $this->tax_rate = (string) $product->tax_rate;
            $this->valuation_method = $product->valuation_method;
            $this->reorder_level = (string) $product->reorder_level;
            $this->reorder_quantity = (string) $product->reorder_quantity;
            $this->is_active = $product->is_active;
            $this->is_purchasable = $product->is_purchasable;
            $this->is_sellable = $product->is_sellable;
            $this->is_stockable = $product->is_stockable;
            $this->attribute_ids = $product->attributes->pluck('id')->toArray();
        }

        $this->showModal = true;
    }

    public function updatedName(): void
    {
        if (! $this->productId) {
            $this->slug = Str::slug($this->name);
        }
    }

    public function save(): void
    {
        $service = app(ProductService::class);

        if ($this->productId) {
            $validated = $this->validate((new UpdateProductRequest)->rules());
            $product = Product::findOrFail($this->productId);
            $this->authorize('update', $product);
            $service->update($product, array_merge($validated, ['attribute_ids' => $this->attribute_ids]));
            $this->dispatch('toast', message: 'Product updated successfully.', type: 'success');
        } else {
            $validated = $this->validate((new StoreProductRequest)->rules());
            $this->authorize('create', Product::class);
            $service->create(array_merge($validated, ['attribute_ids' => $this->attribute_ids]));
            $this->dispatch('toast', message: 'Product created successfully.', type: 'success');
        }

        $this->showModal = false;
        $this->dispatch('productSaved');
    }

    public function render(): View
    {
        return view('livewire.catalog.product-form-modal', [
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'brands' => Brand::where('is_active', true)->orderBy('name')->get(),
            'units' => UnitOfMeasure::where('is_active', true)->orderBy('name')->get(),
            'attributes' => ProductAttribute::orderBy('name')->get(),
        ]);
    }
}
