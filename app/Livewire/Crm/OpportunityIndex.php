<?php

declare(strict_types=1);

namespace App\Livewire\Crm;

use App\Models\CrmPipelineStage;
use App\Models\Customer;
use App\Models\User;
use App\Services\OpportunityService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class OpportunityIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $customerFilter = '';

    #[Url]
    public string $stageFilter = '';

    #[Url]
    public string $assignedToFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCustomerFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStageFilter(): void
    {
        $this->resetPage();
    }

    public function updatedAssignedToFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $opportunities = app(OpportunityService::class)->paginate([
            'search' => $this->search,
            'customer_id' => $this->customerFilter ?: null,
            'pipeline_stage_id' => $this->stageFilter ?: null,
            'assigned_to' => $this->assignedToFilter ?: null,
        ]);

        return view('livewire.crm.opportunity-index', [
            'opportunities' => $opportunities,
            'customers' => Customer::orderBy('name')->get(),
            'stages' => CrmPipelineStage::active()->ordered()->get(),
            'users' => User::orderBy('name')->get(),
        ]);
    }
}
