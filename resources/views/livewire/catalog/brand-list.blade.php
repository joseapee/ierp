<div>
    @section('title', 'Brands')

    <x-page-header title="Brands" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Brands'],
    ]">
        <x-slot:actions>
            @can('brands.create')
                <button class="btn btn-primary btn-wave"
                        wire:click="$dispatch('openBrandFormModal')">
                    <i class="ri-add-line me-1"></i> Add Brand
                </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card custom-card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex gap-2 flex-wrap">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" style="width:220px" placeholder="Search brands...">
                <select wire:model.live="statusFilter" class="form-select form-select-sm" style="width:140px">
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
                            <th>Slug</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($brands as $brand)
                        <tr wire:key="brand-{{ $brand->id }}">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar avatar-sm avatar-rounded bg-primary-transparent">
                                        {{ strtoupper(substr($brand->name, 0, 1)) }}
                                    </span>
                                    <span>{{ $brand->name }}</span>
                                </div>
                            </td>
                            <td><code>{{ $brand->slug }}</code></td>
                            <td>
                                @if($brand->is_active)
                                    <span class="badge bg-success-transparent">Active</span>
                                @else
                                    <span class="badge bg-danger-transparent">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    @can('brands.edit')
                                    <button class="btn btn-sm btn-outline-info btn-wave"
                                            wire:click="$dispatch('openBrandFormModal', { brandId: {{ $brand->id }} })">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    @endcan
                                    @can('brands.delete')
                                    <button class="btn btn-sm btn-outline-danger btn-wave"
                                            wire:click="deleteBrand({{ $brand->id }})"
                                            wire:confirm="Are you sure you want to delete this brand?">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No brands found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($brands->hasPages())
        <div class="card-footer">
            {{ $brands->links() }}
        </div>
        @endif
    </div>

    <livewire:catalog.brand-form-modal />
</div>
