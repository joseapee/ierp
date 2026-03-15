<?php

declare(strict_types=1);

namespace App\Livewire\Crm;

use App\Models\User;
use App\Services\OpportunityService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PipelineBoard extends Component
{
    #[Url]
    public string $assignedToFilter = '';

    public function updatedAssignedToFilter(): void {}

    public function render(): View
    {
        $boardData = app(OpportunityService::class)->getBoardData([
            'assigned_to' => $this->assignedToFilter ?: null,
        ]);

        return view('livewire.crm.pipeline-board', [
            'boardData' => $boardData,
            'users' => User::orderBy('name')->get(),
        ]);
    }
}
