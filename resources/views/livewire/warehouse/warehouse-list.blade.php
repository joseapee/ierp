<div>
    @section('title', 'Warehouses')

    <x-page-header title="Warehouses" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Warehouses'],
    ]">
        <x-slot:actions>
            @can('warehouses.create')
                <button class="btn btn-primary btn-wave"
                        wire:click="$dispatch('openWarehouseFormModal')">
                    <i class="ri-add-line me-1"></i> Add Warehouse
                </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card custom-card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex gap-2 flex-wrap">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" style="width:220px" placeholder="Search warehouses...">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Locations</th>
                            <th>Default</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($warehouses as $warehouse)
                        <tr wire:key="wh-{{ $warehouse->id }}">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar avatar-sm avatar-rounded bg-primary-transparent">
                                        {{ strtoupper(substr($warehouse->name, 0, 1)) }}
                                    </span>
                                    <span>{{ $warehouse->name }}</span>
                                </div>
                            </td>
                            <td><code>{{ $warehouse->code }}</code></td>
                            <td>{{ $warehouse->locations_count }}</td>
                            <td>
                                @if($warehouse->is_default)
                                    <span class="badge bg-primary-transparent">Default</span>
                                @endif
                            </td>
                            <td>
                                @if($warehouse->is_active)
                                    <span class="badge bg-success-transparent">Active</span>
                                @else
                                    <span class="badge bg-danger-transparent">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('warehouses.show', $warehouse) }}"
                                       class="btn btn-sm btn-outline-primary btn-wave" wire:navigate>
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    @can('warehouses.edit')
                                    <button class="btn btn-sm btn-outline-info btn-wave"
                                            wire:click="$dispatch('openWarehouseFormModal', { warehouseId: {{ $warehouse->id }} })">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    @endcan
                                    @can('warehouses.delete')
                                    <button class="btn btn-sm btn-outline-danger btn-wave"
                                            wire:click="deleteWarehouse({{ $warehouse->id }})"
                                            wire:confirm="Are you sure you want to delete this warehouse?">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No warehouses found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($warehouses->hasPages())
        <div class="card-footer">
            {{ $warehouses->links() }}
        </div>
        @endif
    </div>

    <livewire:warehouse.warehouse-form-modal />
</div>
