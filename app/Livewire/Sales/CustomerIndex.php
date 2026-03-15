<?php

declare(strict_types=1);

namespace App\Livewire\Sales;

use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class CustomerIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $activeFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedActiveFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $customers = Customer::query()
            ->when($this->search, fn ($q, $s) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%")
            ))
            ->when($this->activeFilter !== '', fn ($q) => $q->where('is_active', $this->activeFilter === '1'))
            ->latest()
            ->paginate(15);

        return view('livewire.sales.customer-index', [
            'customers' => $customers,
        ]);
    }
}
