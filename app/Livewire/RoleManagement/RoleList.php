<?php

declare(strict_types=1);

namespace App\Livewire\RoleManagement;

use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class RoleList extends Component
{
    protected $listeners = [
        'roleSaved' => '$refresh',
        'roleDeleted' => '$refresh',
    ];

    public function deleteRole(int $id): void
    {
        $role = Role::findOrFail($id);

        $this->authorize('delete', $role);

        $deleted = app(RoleService::class)->delete($role);

        if (! $deleted) {
            $this->dispatch('toast', message: 'System roles cannot be deleted.', type: 'error');

            return;
        }

        $this->dispatch('toast', message: 'Role deleted successfully.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.role-management.role-list', [
            'roles' => app(RoleService::class)->all(),
        ]);
    }
}
