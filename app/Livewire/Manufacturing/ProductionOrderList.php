<?php

declare(strict_types=1);

namespace App\Livewire\Manufacturing;

use App\Services\ProductionService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ProductionOrderList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public string $priorityFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPriorityFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $orders = app(ProductionService::class)->paginate([
            'search' => $this->search,
            'status' => $this->statusFilter ?: null,
            'priority' => $this->priorityFilter ?: null,
        ]);

        return view('livewire.manufacturing.production-order-list', [
            'orders' => $orders,
        ]);
    }
}
