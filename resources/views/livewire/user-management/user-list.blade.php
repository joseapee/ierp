<div>
    @section('title', 'Users')

    <x-page-header title="User Management" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Users'],
    ]">
        <x-slot:actions>
            @can('users.create')
                <button class="btn btn-primary btn-wave"
                        wire:click="$dispatch('openUserFormModal')"
                        data-bs-toggle="tooltip"
                        title="Create a new user">
                    <i class="ri-add-line me-1"></i> Add User
                </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card custom-card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex gap-2 flex-wrap">
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       class="form-control form-control-sm"
                       style="width:220px"
                       placeholder="Search users..."
                       data-bs-toggle="tooltip"
                       title="Search by name or email">

                <select wire:model.live="statusFilter"
                        class="form-select form-select-sm"
                        style="width:140px"
                        data-bs-toggle="tooltip"
                        title="Filter by status">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Roles</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr wire:key="user-{{ $user->id }}">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar avatar-sm avatar-rounded bg-primary-transparent">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </span>
                                    <span>{{ $user->name }}</span>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @foreach($user->roles as $role)
                                    <span class="badge bg-primary-transparent">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success-transparent">Active</span>
                                @else
                                    <span class="badge bg-danger-transparent">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-sm btn-outline-primary btn-wave"
                                            wire:click="$dispatch('openUserViewModal', { userId: {{ $user->id }} })"
                                            data-bs-toggle="tooltip" title="View user details">
                                        <i class="ri-eye-line"></i>
                                    </button>
                                    @can('users.edit')
                                    <button class="btn btn-sm btn-outline-info btn-wave"
                                            wire:click="$dispatch('openUserFormModal', { userId: {{ $user->id }} })"
                                            data-bs-toggle="tooltip" title="Edit user">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    @endcan
                                    @can('users.delete')
                                    <button class="btn btn-sm btn-outline-danger btn-wave"
                                            wire:click="deleteUser({{ $user->id }})"
                                            wire:confirm="Are you sure you want to delete this user?"
                                            data-bs-toggle="tooltip" title="Delete user">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No users found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($users->hasPages())
        <div class="card-footer">
            {{ $users->links() }}
        </div>
        @endif
    </div>

    <livewire:user-management.user-form-modal />
    <livewire:user-management.user-view-modal />
</div>
