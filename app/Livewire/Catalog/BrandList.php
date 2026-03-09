<?php

declare(strict_types=1);

namespace App\Livewire\Catalog;

use App\Models\Brand;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class BrandList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    protected $listeners = [
        'brandSaved' => '$refresh',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function deleteBrand(int $id): void
    {
        $brand = Brand::findOrFail($id);
        $this->authorize('delete', $brand);
        $brand->delete();
        $this->dispatch('toast', message: 'Brand deleted successfully.', type: 'success');
    }

    public function render(): View
    {
        $brands = Brand::query()
            ->when($this->search, fn ($q, $s) => $q->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('slug', 'like', "%{$s}%")))
            ->when($this->statusFilter !== '', fn ($q) => $q->where('is_active', $this->statusFilter === 'active'))
            ->latest()
            ->paginate(15);

        return view('livewire.catalog.brand-list', [
            'brands' => $brands,
        ]);
    }
}
