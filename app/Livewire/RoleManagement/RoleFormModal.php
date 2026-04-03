<?php

declare(strict_types=1);

namespace App\Livewire\RoleManagement;

use App\Http\Requests\RoleManagement\StoreRoleRequest;
use App\Http\Requests\RoleManagement\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class RoleFormModal extends Component
{
    public bool $showModal = false;

    public ?int $roleId = null;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public array $permission_ids = [];

    protected $listeners = [
        'openRoleFormModal' => 'open',
    ];

    public function updatedName(string $value): void
    {
        if (! $this->roleId) {
            $this->slug = Str::slug($value);
        }
    }

    protected function getTenantId(): ?int
    {
        return app()->bound('current.tenant') ? app('current.tenant')->id : null;
    }

    public function open(?int $roleId = null): void
    {
        $this->resetValidation();
        $this->reset(['name', 'slug', 'description', 'permission_ids']);
        $this->roleId = $roleId;

        if ($roleId) {
            $role = Role::with('permissions')->findOrFail($roleId);
            $this->name = $role->name;
            $this->slug = $role->slug;
            $this->description = $role->description ?? '';
            $this->permission_ids = $role->permissions->pluck('id')->toArray();
        }

        $this->showModal = true;
    }

    public function save(): void
    {
        $service = app(RoleService::class);
        $tenantId = $this->getTenantId();

        if ($this->roleId) {
            $rules = (new UpdateRoleRequest)->rules();
            $rules['slug'][3] = Rule::unique('roles', 'slug')->ignore($this->roleId)->where(fn ($q) => $q->where('tenant_id', $tenantId));
            $validated = $this->validate($rules);
            $role = Role::findOrFail($this->roleId);
            $this->authorize('update', $role);
            $service->update($role, array_merge($validated, ['permission_ids' => $this->permission_ids]));
            $this->dispatch('toast', message: 'Role updated successfully.', type: 'success');
        } else {
            $rules = (new StoreRoleRequest)->rules();
            $rules['slug'][] = Rule::unique('roles', 'slug')->where(fn ($q) => $q->where('tenant_id', $tenantId));
            $validated = $this->validate($rules);
            $this->authorize('create', Role::class);
            $service->create(array_merge($validated, ['permission_ids' => $this->permission_ids, 'tenant_id' => $tenantId]));
            $this->dispatch('toast', message: 'Role created successfully.', type: 'success');
        }

        $this->showModal = false;
        $this->dispatch('roleSaved');
    }

    public function render(): View
    {
        $permissions = Permission::query()
            ->orderBy('module')
            ->orderBy('action')
            ->get()
            ->groupBy('module');

        return view('livewire.role-management.role-form-modal', [
            'permissionsByModule' => $permissions,
        ]);
    }
}
