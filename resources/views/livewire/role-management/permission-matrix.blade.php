<div>
    @section('title', 'Permission Matrix')

    <x-page-header title="Permission Matrix" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Roles', 'route' => 'roles.index'],
        ['label' => 'Permission Matrix'],
    ]" />

    <div class="card custom-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th class="bg-light">Permission</th>
                            @foreach($roles as $role)
                            <th class="text-center bg-light">
                                {{ $role->name }}
                                @if($role->is_system)
                                    <span class="badge bg-warning-transparent ms-1 fs-9">System</span>
                                @endif
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($permissions as $permission)
                        <tr>
                            <td>
                                <code>{{ $permission->slug }}</code>
                                <span class="text-muted fs-11 d-block">{{ $permission->description }}</span>
                            </td>
                            @foreach($roles as $role)
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           @checked($role->permissions->contains('id', $permission->id))
                                           wire:click="togglePermission({{ $role->id }}, {{ $permission->id }})"
                                           @disabled($role->is_system)
                                           data-bs-toggle="tooltip"
                                           title="{{ $role->is_system ? 'System roles are read-only' : 'Toggle this permission' }}">
                                </div>
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
