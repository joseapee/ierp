<?php

declare(strict_types=1);

namespace App\Livewire\Catalog;

use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ProductList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $typeFilter = '';

    #[Url]
    public string $statusFilter = '';

    protected $listeners = [
        'productSaved' => '$refresh',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function deleteProduct(int $id): void
    {
        $product = Product::findOrFail($id);
        $this->authorize('delete', $product);
        app(ProductService::class)->delete($product);
        $this->dispatch('toast', message: 'Product deleted successfully.', type: 'success');
    }

    public function render(): View
    {
        $products = app(ProductService::class)->paginate([
            'search' => $this->search,
            'type' => $this->typeFilter,
            'is_active' => $this->statusFilter ?: null,
        ]);

        return view('livewire.catalog.product-list', [
            'products' => $products,
        ]);
    }
}
