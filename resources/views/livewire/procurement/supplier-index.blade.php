<div>
    @section('title', 'Suppliers')

    <x-page-header title="Suppliers" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Suppliers'],
    ]">
        <x-slot:actions>
            @can('suppliers.create')
                <a href="{{ route('procurement.suppliers.create') }}" class="btn btn-primary btn-wave" wire:navigate>
                    <i class="ri-add-line me-1"></i> New Supplier
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card custom-card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex gap-2 flex-wrap">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" style="width:220px" placeholder="Search suppliers...">
                <select wire:model.live="activeFilter" class="form-select form-select-sm" style="width:140px">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Contact Person</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Lead Time</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                        <tr wire:key="sup-{{ $supplier->id }}">
                            <td class="fw-medium">{{ $supplier->name }}</td>
                            <td>{{ $supplier->contact_person ?? '—' }}</td>
                            <td>{{ $supplier->email ?? '—' }}</td>
                            <td>{{ $supplier->phone ?? '—' }}</td>
                            <td>{{ $supplier->lead_time_days ? $supplier->lead_time_days . ' days' : '—' }}</td>
                            <td>
                                <span class="badge bg-{{ $supplier->is_active ? 'success' : 'danger' }}-transparent">
                                    {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                @can('suppliers.edit')
                                <a href="{{ route('procurement.suppliers.edit', $supplier) }}"
                                   class="btn btn-sm btn-outline-primary btn-wave" wire:navigate>
                                    <i class="ri-pencil-line"></i>
                                </a>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No suppliers found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($suppliers->hasPages())
        <div class="card-footer">
            {{ $suppliers->links() }}
        </div>
        @endif
    </div>
</div>
