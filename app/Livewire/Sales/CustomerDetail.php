<?php

declare(strict_types=1);

namespace App\Livewire\Sales;

use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CustomerDetail extends Component
{
    public Customer $customer;

    public function mount(Customer $customer): void
    {
        $this->customer = $customer->load([
            'contacts',
            'opportunities.pipelineStage',
            'salesOrders',
        ]);
    }

    public function render(): View
    {
        return view('livewire.sales.customer-detail');
    }
}
