<?php

declare(strict_types=1);

namespace App\Livewire\TenantManagement;

use App\Http\Requests\TenantManagement\StoreTenantRequest;
use App\Http\Requests\TenantManagement\UpdateTenantRequest;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;

class TenantFormModal extends Component
{
    public bool $showModal = false;

    public ?int $tenantId = null;

    public string $name = '';

    public string $slug = '';

    public string $domain = '';

    public string $plan = 'starter';

    protected $listeners = [
        'openTenantFormModal' => 'open',
    ];

    public function updatedName(string $value): void
    {
        if (! $this->tenantId) {
            $this->slug = Str::slug($value);
        }
    }

    public function open(?int $tenantId = null): void
    {
        $this->resetValidation();
        $this->reset(['name', 'slug', 'domain', 'plan']);
        $this->tenantId = $tenantId;
        $this->plan = 'starter';

        if ($tenantId) {
            $tenant = Tenant::findOrFail($tenantId);
            $this->name = $tenant->name;
            $this->slug = $tenant->slug;
            $this->domain = $tenant->domain ?? '';
            $this->plan = $tenant->plan;
        }

        $this->showModal = true;
    }

    public function save(): void
    {
        $service = app(TenantService::class);

        if ($this->tenantId) {
            $rules = (new UpdateTenantRequest)->rules();
            $rules['slug'][3] = \Illuminate\Validation\Rule::unique('tenants', 'slug')->ignore($this->tenantId);
            $rules['domain'][3] = \Illuminate\Validation\Rule::unique('tenants', 'domain')->ignore($this->tenantId);
            $validated = $this->validate($rules);
            $tenant = Tenant::findOrFail($this->tenantId);
            $service->update($tenant, $validated);
            $this->dispatch('toast', message: 'Tenant updated successfully.', type: 'success');
        } else {
            $validated = $this->validate((new StoreTenantRequest)->rules());
            $service->create($validated);
            $this->dispatch('toast', message: 'Tenant created successfully.', type: 'success');
        }

        $this->showModal = false;
        $this->dispatch('tenantSaved');
    }

    public function render(): View
    {
        return view('livewire.tenant-management.tenant-form-modal');
    }
}
