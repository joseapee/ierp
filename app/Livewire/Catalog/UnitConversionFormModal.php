<?php

declare(strict_types=1);

namespace App\Livewire\Catalog;

use App\Http\Requests\Catalog\StoreUnitConversionRequest;
use App\Http\Requests\Catalog\UpdateUnitConversionRequest;
use App\Models\UnitConversion;
use App\Models\UnitOfMeasure;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class UnitConversionFormModal extends Component
{
    public bool $showModal = false;

    public ?int $conversionId = null;

    public string $from_unit_id = '';

    public string $to_unit_id = '';

    public string $factor = '';

    protected $listeners = [
        'openConversionFormModal' => 'open',
    ];

    public function open(?int $conversionId = null): void
    {
        $this->resetValidation();
        $this->reset(['from_unit_id', 'to_unit_id', 'factor']);
        $this->conversionId = $conversionId;

        if ($conversionId) {
            $conversion = UnitConversion::findOrFail($conversionId);
            $this->from_unit_id = (string) $conversion->from_unit_id;
            $this->to_unit_id = (string) $conversion->to_unit_id;
            $this->factor = (string) $conversion->factor;
        }

        $this->showModal = true;
    }

    public function save(): void
    {
        if ($this->conversionId) {
            $validated = $this->validate((new UpdateUnitConversionRequest)->rules(), (new UpdateUnitConversionRequest)->messages());
            $conversion = UnitConversion::findOrFail($this->conversionId);
            $this->authorize('update', $conversion);
            $conversion->update($validated);
            $this->dispatch('toast', message: 'Conversion updated successfully.', type: 'success');
        } else {
            $validated = $this->validate((new StoreUnitConversionRequest)->rules(), (new StoreUnitConversionRequest)->messages());
            $this->authorize('create', UnitConversion::class);
            UnitConversion::create($validated);
            $this->dispatch('toast', message: 'Conversion created successfully.', type: 'success');
        }

        $this->showModal = false;
        $this->dispatch('conversionSaved');
    }

    public function render(): View
    {
        $units = UnitOfMeasure::query()
            ->where('is_active', true)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('livewire.catalog.unit-conversion-form-modal', [
            'units' => $units,
        ]);
    }
}
