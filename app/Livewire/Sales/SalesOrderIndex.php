<?php

declare(strict_types=1);

namespace App\Livewire\Sales;

use App\Models\Customer;
use App\Models\SalesOrder;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class SalesOrderIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public string $customerFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCustomerFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $orders = SalesOrder::query()
            ->with(['customer'])
            ->when($this->search, fn ($q, $s) => $q->where(fn ($q) => $q
                ->where('order_number', 'like', "%{$s}%")
                ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$s}%"))
            ))
            ->when($this->statusFilter, fn ($q, $v) => $q->where('status', $v))
            ->when($this->customerFilter, fn ($q, $v) => $q->where('customer_id', $v))
            ->latest()
            ->paginate(15);

        return view('livewire.sales.sales-order-index', [
            'orders' => $orders,
            'customers' => Customer::query()->active()->orderBy('name')->get(),
        ]);
    }
}
