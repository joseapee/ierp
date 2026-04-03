<div>
    @section('title', 'Roles')

    <x-page-header title="Role Management" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Roles'],
    ]">
        <x-slot:actions>
            @can('roles.create')
                <button class="btn btn-primary btn-wave"
                        wire:click="$dispatch('openRoleFormModal')"
                        data-bs-toggle="tooltip"
                        title="Create a new role">
                    <i class="ri-add-line me-1"></i> Add Role
                </button>
            @endcan
            @can('roles.manage-permissions')
                <a href="{{ route('roles.permissions') }}"
                   class="btn btn-outline-primary btn-wave ms-2"
                   wire:navigate
                   data-bs-toggle="tooltip"
                   title="View permission matrix for all roles">
                    <i class="ri-shield-keyhole-line me-1"></i> Permission Matrix
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card custom-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Permissions</th>
                            <th>Users</th>
                            <th>Type</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                        <tr wire:key="role-{{ $role->id }}">
                            <td class="fw-medium">{{ $role->name }}</td>
                            <td><code>{{ $role->slug }}</code></td>
                            <td>
                                <span class="badge bg-info-transparent">{{ $role->permissions->count() }}</span>
                            </td>
                            <td>{{ $role->users_count }}</td>
                            <td>
                                @if($role->is_system)
                                    <span class="badge bg-warning-transparent">System</span>
                                @else
                                    <span class="badge bg-secondary-transparent">Custom</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    @can('roles.edit')
                                    <button class="btn btn-sm btn-outline-info btn-wave"
                                            wire:click="$dispatch('openRoleFormModal', { roleId: {{ $role->id }} })"
                                            @if($role->is_system) disabled @endif
                                            data-bs-toggle="tooltip" title="{{ $role->is_system ? 'System roles cannot be edited' : 'Edit role' }}">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    @endcan
                                    @can('roles.delete')
                                    <button class="btn btn-sm btn-outline-danger btn-wave"
                                            wire:click="deleteRole({{ $role->id }})"
                                            wire:confirm="Are you sure you want to delete this role?"
                                            @if($role->is_system) disabled @endif
                                            data-bs-toggle="tooltip" title="{{ $role->is_system ? 'System roles cannot be deleted' : 'Delete role' }}">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No roles found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <livewire:role-management.role-form-modal />
</div>
