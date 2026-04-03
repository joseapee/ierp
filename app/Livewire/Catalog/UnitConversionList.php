<?php

declare(strict_types=1);

namespace App\Livewire\Catalog;

use App\Models\UnitConversion;
use App\Models\UnitOfMeasure;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class UnitConversionList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $typeFilter = '';

    protected $listeners = [
        'conversionSaved' => '$refresh',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function deleteConversion(int $id): void
    {
        $conversion = UnitConversion::findOrFail($id);
        $this->authorize('delete', $conversion);
        $conversion->delete();
        $this->dispatch('toast', message: 'Conversion deleted successfully.', type: 'success');
    }

    public function render(): View
    {
        $conversions = UnitConversion::query()
            ->with(['fromUnit', 'toUnit'])
            ->when($this->search, fn ($q, $s) => $q->whereHas('fromUnit', fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('abbreviation', 'like', "%{$s}%"))
                ->orWhereHas('toUnit', fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('abbreviation', 'like', "%{$s}%")))
            ->when($this->typeFilter, fn ($q, $t) => $q->whereHas('fromUnit', fn ($q) => $q->where('type', $t)))
            ->latest()
            ->paginate(15);

        $types = UnitOfMeasure::query()
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        return view('livewire.catalog.unit-conversion-list', [
            'conversions' => $conversions,
            'types' => $types,
        ]);
    }
}
