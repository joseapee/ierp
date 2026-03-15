<?php

declare(strict_types=1);

namespace App\Livewire\Crm;

use App\Models\User;
use App\Services\LeadService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class LeadIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public string $sourceFilter = '';

    #[Url]
    public string $assignedToFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSourceFilter(): void
    {
        $this->resetPage();
    }

    public function updatedAssignedToFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $leads = app(LeadService::class)->paginate([
            'search' => $this->search,
            'status' => $this->statusFilter ?: null,
            'source' => $this->sourceFilter ?: null,
            'assigned_to' => $this->assignedToFilter ?: null,
        ]);

        return view('livewire.crm.lead-index', [
            'leads' => $leads,
            'users' => User::orderBy('name')->get(),
        ]);
    }
}
