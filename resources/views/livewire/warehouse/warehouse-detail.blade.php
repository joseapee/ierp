<div>
    @section('title', $warehouse->name)

    <x-page-header :title="$warehouse->name" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Warehouses', 'route' => 'warehouses.index'],
        ['label' => $warehouse->name],
    ]">
        <x-slot:actions>
            @can('warehouses.edit')
                <button class="btn btn-primary btn-wave"
                        wire:click="$dispatch('openWarehouseFormModal', { warehouseId: {{ $warehouse->id }} })">
                    <i class="ri-edit-line me-1"></i> Edit
                </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="row">
        <div class="col-xl-4">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Warehouse Info</div>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Code</span>
                            <code>{{ $warehouse->code }}</code>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Address</span>
                            <span>{{ $warehouse->address ?? '—' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Phone</span>
                            <span>{{ $warehouse->phone ?? '—' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Email</span>
                            <span>{{ $warehouse->email ?? '—' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Default</span>
                            @if($warehouse->is_default)
                                <span class="badge bg-primary-transparent">Yes</span>
                            @else
                                <span class="text-muted">No</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Status</span>
                            @if($warehouse->is_active)
                                <span class="badge bg-success-transparent">Active</span>
                            @else
                                <span class="badge bg-danger-transparent">Inactive</span>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            {{-- Locations --}}
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Locations ({{ $warehouse->locations->count() }})</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($warehouse->locations as $location)
                                <tr>
                                    <td><code>{{ $location->code }}</code></td>
                                    <td>{{ $location->name }}</td>
                                    <td><span class="badge bg-info-transparent">{{ ucfirst($location->type ?? 'bin') }}</span></td>
                                    <td>
                                        @if($location->is_active)
                                            <span class="badge bg-success-transparent">Active</span>
                                        @else
                                            <span class="badge bg-danger-transparent">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No locations defined.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Stock Batches --}}
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Current Stock</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Batch #</th>
                                    <th class="text-end">Remaining</th>
                                    <th>Expiry</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($warehouse->stockBatches as $batch)
                                <tr>
                                    <td>{{ $batch->product?->name }}</td>
                                    <td><code>{{ $batch->batch_number ?? '—' }}</code></td>
                                    <td class="text-end">{{ number_format((float) $batch->remaining_quantity, 2) }}</td>
                                    <td>{{ $batch->expiry_date?->format('Y-m-d') ?? '—' }}</td>
                                    <td><span class="badge bg-{{ $batch->status === 'available' ? 'success' : 'warning' }}-transparent">{{ ucfirst($batch->status) }}</span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No stock in this warehouse.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <livewire:warehouse.warehouse-form-modal />
</div>
