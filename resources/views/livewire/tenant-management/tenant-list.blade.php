<div>
    @section('title', 'Tenants')

    <x-page-header title="Tenant Management" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Tenants'],
    ]">
        <x-slot:actions>
            <button class="btn btn-primary btn-wave"
                    wire:click="$dispatch('openTenantFormModal')"
                    data-bs-toggle="tooltip"
                    title="Create a new tenant">
                <i class="ri-add-line me-1"></i> Add Tenant
            </button>
        </x-slot:actions>
    </x-page-header>

    <div class="card custom-card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex gap-2 flex-wrap">
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       class="form-control form-control-sm"
                       style="width:220px"
                       placeholder="Search tenants..."
                       data-bs-toggle="tooltip"
                       title="Search by name, slug or domain">

                <select wire:model.live="statusFilter"
                        class="form-select form-select-sm"
                        style="width:140px"
                        data-bs-toggle="tooltip"
                        title="Filter by status">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                </select>

                <select wire:model.live="planFilter"
                        class="form-select form-select-sm"
                        style="width:140px"
                        data-bs-toggle="tooltip"
                        title="Filter by plan">
                    <option value="">All Plans</option>
                    <option value="starter">Starter</option>
                    <option value="pro">Pro</option>
                    <option value="enterprise">Enterprise</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Domain</th>
                            <th>Plan</th>
                            <th>Users</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenants as $tenant)
                        <tr wire:key="tenant-{{ $tenant->id }}">
                            <td class="fw-medium">{{ $tenant->name }}</td>
                            <td><code>{{ $tenant->slug }}</code></td>
                            <td>{{ $tenant->domain ?: '—' }}</td>
                            <td>
                                <span class="badge bg-primary-transparent text-capitalize">{{ $tenant->plan }}</span>
                            </td>
                            <td>{{ $tenant->users_count }}</td>
                            <td>
                                @if($tenant->status === 'active')
                                    <span class="badge bg-success-transparent">Active</span>
                                @else
                                    <span class="badge bg-danger-transparent text-capitalize">{{ $tenant->status }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-sm btn-outline-info btn-wave"
                                            wire:click="$dispatch('openTenantFormModal', { tenantId: {{ $tenant->id }} })"
                                            data-bs-toggle="tooltip" title="Edit tenant">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    <button class="btn btn-sm {{ $tenant->isActive() ? 'btn-outline-warning' : 'btn-outline-success' }} btn-wave"
                                            wire:click="toggleStatus({{ $tenant->id }})"
                                            wire:confirm="{{ $tenant->isActive() ? 'Suspend this tenant?' : 'Activate this tenant?' }}"
                                            data-bs-toggle="tooltip"
                                            title="{{ $tenant->isActive() ? 'Suspend tenant' : 'Activate tenant' }}">
                                        <i class="{{ $tenant->isActive() ? 'ri-pause-circle-line' : 'ri-play-circle-line' }}"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No tenants found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($tenants->hasPages())
        <div class="card-footer">
            {{ $tenants->links() }}
        </div>
        @endif
    </div>

    <livewire:tenant-management.tenant-form-modal />
</div>
