<?php

declare(strict_types=1);

namespace App\Livewire\Catalog;

use App\Models\UnitOfMeasure;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class UnitOfMeasureList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $typeFilter = '';

    protected $listeners = [
        'unitSaved' => '$refresh',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function deleteUnit(int $id): void
    {
        $unit = UnitOfMeasure::findOrFail($id);
        $this->authorize('delete', $unit);
        $unit->delete();
        $this->dispatch('toast', message: 'Unit deleted successfully.', type: 'success');
    }

    public function render(): View
    {
        $units = UnitOfMeasure::query()
            ->when($this->search, fn ($q, $s) => $q->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('abbreviation', 'like', "%{$s}%")))
            ->when($this->typeFilter, fn ($q, $t) => $q->where('type', $t))
            ->latest()
            ->paginate(15);

        return view('livewire.catalog.unit-of-measure-list', [
            'units' => $units,
        ]);
    }
}
