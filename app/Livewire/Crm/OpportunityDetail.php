<?php

declare(strict_types=1);

namespace App\Livewire\Crm;

use App\Models\CrmPipelineStage;
use App\Models\Opportunity;
use App\Services\OpportunityService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class OpportunityDetail extends Component
{
    public Opportunity $opportunity;

    public bool $showMarkWonModal = false;

    public bool $showMarkLostModal = false;

    public string $lost_reason = '';

    public string $selectedStageId = '';

    public function mount(Opportunity $opportunity): void
    {
        $this->opportunity = $opportunity->load([
            'customer', 'contact', 'pipelineStage', 'assignedUser', 'salesOrder',
        ]);
        $this->selectedStageId = (string) $opportunity->pipeline_stage_id;
    }

    public function moveToStage(): void
    {
        if (! $this->selectedStageId || (int) $this->selectedStageId === $this->opportunity->pipeline_stage_id) {
            return;
        }

        $service = app(OpportunityService::class);

        try {
            $this->opportunity = $service->moveToStage($this->opportunity, (int) $this->selectedStageId);
            $this->opportunity->load(['customer', 'contact', 'pipelineStage', 'assignedUser', 'salesOrder']);
            $this->dispatch('toast', message: 'Opportunity moved to new stage.', type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function openMarkWonModal(): void
    {
        $this->showMarkWonModal = true;
    }

    public function markWon(): void
    {
        $service = app(OpportunityService::class);

        try {
            $this->opportunity = $service->markWon($this->opportunity);
            $this->opportunity->load(['customer', 'contact', 'pipelineStage', 'assignedUser', 'salesOrder']);
            $this->showMarkWonModal = false;
            $this->dispatch('toast', message: 'Opportunity marked as won.', type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function openMarkLostModal(): void
    {
        $this->lost_reason = '';
        $this->showMarkLostModal = true;
    }

    public function markLost(): void
    {
        $this->validate(['lost_reason' => 'required|string|min:3']);

        $service = app(OpportunityService::class);

        try {
            $this->opportunity = $service->markLost($this->opportunity, $this->lost_reason);
            $this->opportunity->load(['customer', 'contact', 'pipelineStage', 'assignedUser', 'salesOrder']);
            $this->showMarkLostModal = false;
            $this->dispatch('toast', message: 'Opportunity marked as lost.', type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function render(): View
    {
        return view('livewire.crm.opportunity-detail', [
            'stages' => CrmPipelineStage::active()->ordered()->get(),
        ]);
    }
}
