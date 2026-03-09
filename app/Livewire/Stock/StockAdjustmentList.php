<?php

declare(strict_types=1);

namespace App\Livewire\Stock;

use App\Models\StockAdjustment;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class StockAdjustmentList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $adjustments = StockAdjustment::query()
            ->with(['warehouse', 'adjustedBy'])
            ->when($this->search, fn ($q, $s) => $q->where(fn ($q) => $q->where('adjustment_number', 'like', "%{$s}%")->orWhere('reason', 'like', "%{$s}%")))
            ->when($this->statusFilter, fn ($q, $st) => $q->where('status', $st))
            ->latest()
            ->paginate(15);

        return view('livewire.stock.stock-adjustment-list', [
            'adjustments' => $adjustments,
        ]);
    }
}
