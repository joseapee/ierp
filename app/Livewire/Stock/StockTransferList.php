<?php

declare(strict_types=1);

namespace App\Livewire\Stock;

use App\Models\StockTransfer;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class StockTransferList extends Component
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
        $transfers = StockTransfer::query()
            ->with(['fromWarehouse', 'toWarehouse', 'initiatedBy'])
            ->when($this->search, fn ($q, $s) => $q->where('transfer_number', 'like', "%{$s}%"))
            ->when($this->statusFilter, fn ($q, $st) => $q->where('status', $st))
            ->latest()
            ->paginate(15);

        return view('livewire.stock.stock-transfer-list', [
            'transfers' => $transfers,
        ]);
    }
}
