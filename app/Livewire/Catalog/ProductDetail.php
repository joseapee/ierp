<?php

declare(strict_types=1);

namespace App\Livewire\Catalog;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ProductDetail extends Component
{
    public Product $product;

    protected $listeners = [
        'variantsSaved' => '$refresh',
        'productSaved' => '$refresh',
    ];

    public function mount(Product $product): void
    {
        $this->product = $product->load([
            'category', 'brand', 'baseUnit', 'variants.attributeValues.attribute',
            'attributes.values', 'stockBatches.warehouse',
        ]);
    }

    public function render(): View
    {
        return view('livewire.catalog.product-detail');
    }
}
