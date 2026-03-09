<?php

declare(strict_types=1);

namespace App\Livewire\Warehouse;

use App\Http\Requests\Warehouse\StoreWarehouseRequest;
use App\Http\Requests\Warehouse\UpdateWarehouseRequest;
use App\Models\Warehouse;
use App\Services\WarehouseService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class WarehouseFormModal extends Component
{
    public bool $showModal = false;

    public ?int $warehouseId = null;

    public string $name = '';

    public string $code = '';

    public string $address = '';

    public string $phone = '';

    public string $email = '';

    public bool $is_default = false;

    public bool $is_active = true;

    protected $listeners = [
        'openWarehouseFormModal' => 'open',
    ];

    public function open(?int $warehouseId = null): void
    {
        $this->resetValidation();
        $this->reset(['name', 'code', 'address', 'phone', 'email', 'is_default', 'is_active']);
        $this->warehouseId = $warehouseId;
        $this->is_active = true;

        if ($warehouseId) {
            $warehouse = Warehouse::findOrFail($warehouseId);
            $this->name = $warehouse->name;
            $this->code = $warehouse->code;
            $this->address = $warehouse->address ?? '';
            $this->phone = $warehouse->phone ?? '';
            $this->email = $warehouse->email ?? '';
            $this->is_default = $warehouse->is_default;
            $this->is_active = $warehouse->is_active;
        }

        $this->showModal = true;
    }

    public function save(): void
    {
        $service = app(WarehouseService::class);

        if ($this->warehouseId) {
            $validated = $this->validate((new UpdateWarehouseRequest)->rules());
            $warehouse = Warehouse::findOrFail($this->warehouseId);
            $this->authorize('update', $warehouse);
            $service->update($warehouse, $validated);
            $this->dispatch('toast', message: 'Warehouse updated successfully.', type: 'success');
        } else {
            $validated = $this->validate((new StoreWarehouseRequest)->rules());
            $this->authorize('create', Warehouse::class);
            $service->create($validated);
            $this->dispatch('toast', message: 'Warehouse created successfully.', type: 'success');
        }

        $this->showModal = false;
        $this->dispatch('warehouseSaved');
    }

    public function render(): View
    {
        return view('livewire.warehouse.warehouse-form-modal');
    }
}
