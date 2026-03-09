<?php

declare(strict_types=1);

namespace App\Livewire\RoleManagement;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PermissionMatrix extends Component
{
    public function togglePermission(int $roleId, int $permissionId): void
    {
        $role = Role::findOrFail($roleId);

        $this->authorize('managePermissions', $role);

        if ($role->is_system) {
            $this->dispatch('toast', message: 'System roles cannot be modified here.', type: 'error');

            return;
        }

        $role->permissions()->toggle([$permissionId]);

        $this->dispatch('toast', message: 'Permission updated.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.role-management.permission-matrix', [
            'roles' => Role::with('permissions')->orderBy('name')->get(),
            'permissions' => Permission::query()->orderBy('module')->orderBy('action')->get(),
        ]);
    }
}
