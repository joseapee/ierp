<?php

declare(strict_types=1);

namespace App\Livewire\Crm;

use App\Models\CrmContact;
use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ContactIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $customerFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCustomerFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $contacts = CrmContact::query()
            ->with('customer')
            ->when($this->search, fn ($q, $s) => $q->where(fn ($q) => $q
                ->where('first_name', 'like', "%{$s}%")
                ->orWhere('last_name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
            ))
            ->when($this->customerFilter, fn ($q, $v) => $q->where('customer_id', $v))
            ->latest()
            ->paginate(15);

        return view('livewire.crm.contact-index', [
            'contacts' => $contacts,
            'customers' => Customer::orderBy('name')->get(),
        ]);
    }
}
