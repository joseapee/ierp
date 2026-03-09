<?php

declare(strict_types=1);

namespace App\Livewire\Catalog;

use App\Http\Requests\Catalog\StoreUnitOfMeasureRequest;
use App\Http\Requests\Catalog\UpdateUnitOfMeasureRequest;
use App\Models\UnitOfMeasure;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class UnitOfMeasureFormModal extends Component
{
    public bool $showModal = false;

    public ?int $unitId = null;

    public string $name = '';

    public string $abbreviation = '';

    public string $type = 'quantity';

    public bool $is_base_unit = false;

    public bool $is_active = true;

    protected $listeners = [
        'openUnitFormModal' => 'open',
    ];

    public function open(?int $unitId = null): void
    {
        $this->resetValidation();
        $this->reset(['name', 'abbreviation', 'type', 'is_base_unit', 'is_active']);
        $this->unitId = $unitId;
        $this->is_active = true;
        $this->type = 'quantity';

        if ($unitId) {
            $unit = UnitOfMeasure::findOrFail($unitId);
            $this->name = $unit->name;
            $this->abbreviation = $unit->abbreviation;
            $this->type = $unit->type;
            $this->is_base_unit = $unit->is_base_unit;
            $this->is_active = $unit->is_active;
        }

        $this->showModal = true;
    }

    public function save(): void
    {
        if ($this->unitId) {
            $validated = $this->validate((new UpdateUnitOfMeasureRequest)->rules());
            $unit = UnitOfMeasure::findOrFail($this->unitId);
            $this->authorize('update', $unit);
            $unit->update($validated);
            $this->dispatch('toast', message: 'Unit updated successfully.', type: 'success');
        } else {
            $validated = $this->validate((new StoreUnitOfMeasureRequest)->rules());
            $this->authorize('create', UnitOfMeasure::class);
            UnitOfMeasure::create($validated);
            $this->dispatch('toast', message: 'Unit created successfully.', type: 'success');
        }

        $this->showModal = false;
        $this->dispatch('unitSaved');
    }

    public function render(): View
    {
        return view('livewire.catalog.unit-of-measure-form-modal');
    }
}
