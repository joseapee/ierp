<?php

declare(strict_types=1);

namespace App\Livewire\Warehouse;

use App\Models\Warehouse;
use App\Services\WarehouseService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class WarehouseList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    protected $listeners = [
        'warehouseSaved' => '$refresh',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function deleteWarehouse(int $id): void
    {
        $warehouse = Warehouse::findOrFail($id);
        $this->authorize('delete', $warehouse);
        app(WarehouseService::class)->delete($warehouse);
        $this->dispatch('toast', message: 'Warehouse deleted successfully.', type: 'success');
    }

    public function render(): View
    {
        $warehouses = Warehouse::query()
            ->withCount('locations')
            ->when($this->search, fn ($q, $s) => $q->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%")))
            ->latest()
            ->paginate(15);

        return view('livewire.warehouse.warehouse-list', [
            'warehouses' => $warehouses,
        ]);
    }
}
