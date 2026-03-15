<?php

declare(strict_types=1);

namespace App\Livewire\Crm;

use App\Models\CrmActivity;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ActivityIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $typeFilter = '';

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public string $assignedToFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedAssignedToFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $activities = CrmActivity::query()
            ->with('assignedUser')
            ->when($this->search, fn ($q, $s) => $q->where('subject', 'like', "%{$s}%"))
            ->when($this->typeFilter, fn ($q, $v) => $q->where('type', $v))
            ->when($this->statusFilter, fn ($q, $v) => $q->where('status', $v))
            ->when($this->assignedToFilter, fn ($q, $v) => $q->where('assigned_to', $v))
            ->latest('due_date')
            ->paginate(15);

        return view('livewire.crm.activity-index', [
            'activities' => $activities,
            'users' => User::orderBy('name')->get(),
        ]);
    }
}
