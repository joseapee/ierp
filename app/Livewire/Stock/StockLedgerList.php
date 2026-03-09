<?php

declare(strict_types=1);

namespace App\Livewire\Stock;

use App\Models\StockLedger;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class StockLedgerList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $movementType = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedMovementType(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $entries = StockLedger::query()
            ->with(['product', 'warehouse', 'createdBy'])
            ->when($this->search, fn ($q, $s) => $q->whereHas('product', fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%")))
            ->when($this->movementType, fn ($q, $t) => $q->where('movement_type', $t))
            ->latest()
            ->paginate(25);

        return view('livewire.stock.stock-ledger-list', [
            'entries' => $entries,
        ]);
    }
}
