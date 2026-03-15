<?php

declare(strict_types=1);

namespace App\Livewire\Crm;

use App\Models\CrmPipelineStage;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PipelineStageManager extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public int $display_order = 10;

    public string $win_probability = '0';

    public string $color = '#6c757d';

    public bool $is_won = false;

    public bool $is_lost = false;

    public bool $is_active = true;

    public function openModal(?int $stageId = null): void
    {
        $this->resetValidation();

        if ($stageId) {
            $stage = CrmPipelineStage::findOrFail($stageId);
            $this->editingId = $stage->id;
            $this->name = $stage->name;
            $this->display_order = $stage->display_order;
            $this->win_probability = (string) $stage->win_probability;
            $this->color = $stage->color ?? '#6c757d';
            $this->is_won = $stage->is_won;
            $this->is_lost = $stage->is_lost;
            $this->is_active = $stage->is_active;
        } else {
            $this->reset(['editingId', 'name']);
            $this->display_order = 10;
            $this->win_probability = '0';
            $this->color = '#6c757d';
            $this->is_won = false;
            $this->is_lost = false;
            $this->is_active = true;
        }

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'display_order' => 'required|integer|min:0',
            'win_probability' => 'required|numeric|min:0|max:100',
            'color' => 'required|string|max:7',
        ]);

        $data = [
            'name' => $this->name,
            'display_order' => $this->display_order,
            'win_probability' => (float) $this->win_probability,
            'color' => $this->color,
            'is_won' => $this->is_won,
            'is_lost' => $this->is_lost,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            CrmPipelineStage::findOrFail($this->editingId)->update($data);
        } else {
            CrmPipelineStage::create($data);
        }

        $this->showModal = false;
        $this->dispatch('toast', message: $this->editingId ? 'Stage updated.' : 'Stage created.', type: 'success');
    }

    public function deleteStage(int $id): void
    {
        $stage = CrmPipelineStage::findOrFail($id);

        if ($stage->opportunities()->exists()) {
            $this->dispatch('toast', message: 'Cannot delete stage with linked opportunities.', type: 'error');

            return;
        }

        $stage->delete();
        $this->dispatch('toast', message: 'Stage deleted.', type: 'success');
    }

    public function toggleActive(int $id): void
    {
        $stage = CrmPipelineStage::findOrFail($id);
        $stage->update(['is_active' => ! $stage->is_active]);
    }

    public function render(): View
    {
        $stages = CrmPipelineStage::query()
            ->orderBy('display_order')
            ->get();

        return view('livewire.crm.pipeline-stage-manager', [
            'stages' => $stages,
        ]);
    }
}
