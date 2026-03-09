<?php

declare(strict_types=1);

namespace App\Livewire\Manufacturing;

use App\Models\ProductionStage;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ProductionStageManager extends Component
{
    public string $industryFilter = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $code = '';

    public string $description = '';

    public string $industry_type = 'general';

    public int $sort_order = 10;

    public int $estimated_duration_minutes = 0;

    public bool $is_active = true;

    public function openModal(?int $stageId = null): void
    {
        $this->resetValidation();

        if ($stageId) {
            $stage = ProductionStage::findOrFail($stageId);
            $this->editingId = $stage->id;
            $this->name = $stage->name;
            $this->code = $stage->code ?? '';
            $this->description = $stage->description ?? '';
            $this->industry_type = $stage->industry_type;
            $this->sort_order = $stage->sort_order;
            $this->estimated_duration_minutes = $stage->estimated_duration_minutes ?? 0;
            $this->is_active = $stage->is_active;
        } else {
            $this->reset(['editingId', 'name', 'code', 'description', 'estimated_duration_minutes']);
            $this->industry_type = 'general';
            $this->sort_order = 10;
            $this->is_active = true;
        }

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'industry_type' => ['required', 'string'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $data = [
            'name' => $this->name,
            'code' => $this->code ?: null,
            'description' => $this->description ?: null,
            'industry_type' => $this->industry_type,
            'sort_order' => $this->sort_order,
            'estimated_duration_minutes' => $this->estimated_duration_minutes ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            ProductionStage::findOrFail($this->editingId)->update($data);
        } else {
            ProductionStage::create($data);
        }

        $this->showModal = false;
        $this->dispatch('toast', message: $this->editingId ? 'Stage updated.' : 'Stage created.', type: 'success');
    }

    public function deleteStage(int $id): void
    {
        $stage = ProductionStage::findOrFail($id);

        if ($stage->tasks()->exists()) {
            $this->dispatch('toast', message: 'Cannot delete stage with assigned tasks.', type: 'error');

            return;
        }

        $stage->delete();
        $this->dispatch('toast', message: 'Stage deleted.', type: 'success');
    }

    public function toggleActive(int $id): void
    {
        $stage = ProductionStage::findOrFail($id);
        $stage->update(['is_active' => ! $stage->is_active]);
    }

    public function render(): View
    {
        $stages = ProductionStage::query()
            ->when($this->industryFilter, fn ($q, $v) => $q->where('industry_type', $v))
            ->orderBy('industry_type')
            ->orderBy('sort_order')
            ->get();

        $industries = ProductionStage::query()
            ->select('industry_type')
            ->distinct()
            ->pluck('industry_type');

        return view('livewire.manufacturing.production-stage-manager', [
            'stages' => $stages,
            'industries' => $industries,
        ]);
    }
}
