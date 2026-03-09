<div>
    @section('title', 'Units of Measure')

    <x-page-header title="Units of Measure" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Units of Measure'],
    ]">
        <x-slot:actions>
            @can('units.create')
                <button class="btn btn-primary btn-wave"
                        wire:click="$dispatch('openUnitFormModal')">
                    <i class="ri-add-line me-1"></i> Add Unit
                </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card custom-card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex gap-2 flex-wrap">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" style="width:220px" placeholder="Search units...">
                <select wire:model.live="typeFilter" class="form-select form-select-sm" style="width:140px">
                    <option value="">All Types</option>
                    <option value="weight">Weight</option>
                    <option value="length">Length</option>
                    <option value="volume">Volume</option>
                    <option value="area">Area</option>
                    <option value="quantity">Quantity</option>
                    <option value="time">Time</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Abbreviation</th>
                            <th>Type</th>
                            <th>Base Unit</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($units as $unit)
                        <tr wire:key="unit-{{ $unit->id }}">
                            <td>{{ $unit->name }}</td>
                            <td><code>{{ $unit->abbreviation }}</code></td>
                            <td><span class="badge bg-info-transparent">{{ ucfirst($unit->type) }}</span></td>
                            <td>
                                @if($unit->is_base_unit)
                                    <span class="badge bg-primary-transparent">Yes</span>
                                @else
                                    <span class="text-muted">No</span>
                                @endif
                            </td>
                            <td>
                                @if($unit->is_active)
                                    <span class="badge bg-success-transparent">Active</span>
                                @else
                                    <span class="badge bg-danger-transparent">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    @can('units.edit')
                                    <button class="btn btn-sm btn-outline-info btn-wave"
                                            wire:click="$dispatch('openUnitFormModal', { unitId: {{ $unit->id }} })">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    @endcan
                                    @can('units.delete')
                                    <button class="btn btn-sm btn-outline-danger btn-wave"
                                            wire:click="deleteUnit({{ $unit->id }})"
                                            wire:confirm="Are you sure you want to delete this unit?">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No units found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($units->hasPages())
        <div class="card-footer">
            {{ $units->links() }}
        </div>
        @endif
    </div>

    <livewire:catalog.unit-of-measure-form-modal />
</div>
