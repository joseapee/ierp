<div>
    @section('title', 'Bill of Materials')

    <x-page-header title="Bill of Materials" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Bill of Materials'],
    ]">
        <x-slot:actions>
            @can('bom.create')
                <button class="btn btn-primary btn-wave"
                        wire:click="$dispatch('openBomFormModal')">
                    <i class="ri-add-line me-1"></i> New BOM
                </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card custom-card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex gap-2 flex-wrap">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" style="width:220px" placeholder="Search BOMs...">
                <select wire:model.live="statusFilter" class="form-select form-select-sm" style="width:140px">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
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
                            <th>BOM Name</th>
                            <th>Product</th>
                            <th>Version</th>
                            <th class="text-end">Materials</th>
                            <th class="text-end">Est. Cost</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($boms as $bom)
                        <tr wire:key="bom-{{ $bom->id }}">
                            <td>
                                <span class="fw-medium">{{ $bom->name }}</span>
                            </td>
                            <td>{{ $bom->product?->name }}</td>
                            <td><code>v{{ $bom->version }}</code></td>
                            <td class="text-end">{{ $bom->items->count() }}</td>
                            <td class="text-end">
                                @php
                                    $cost = $bom->items->sum(fn($item) => (float)$item->quantity * (float)$item->unit_cost * (1 + (float)$item->wastage_percentage / 100));
                                    $yieldQty = (float)$bom->yield_quantity ?: 1;
                                @endphp
                                {{ number_format($cost / $yieldQty, 2) }}
                            </td>
                            <td>
                                <span class="badge bg-{{ $bom->status === 'active' ? 'success' : ($bom->status === 'draft' ? 'warning' : 'secondary') }}-transparent">
                                    {{ ucfirst($bom->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    @can('bom.edit')
                                    <button class="btn btn-sm btn-outline-info btn-wave"
                                            wire:click="$dispatch('openBomFormModal', { bomId: {{ $bom->id }} })">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    @endcan
                                    @if($bom->status === 'draft')
                                        @can('bom.edit')
                                        <button class="btn btn-sm btn-outline-success btn-wave"
                                                wire:click="activateBom({{ $bom->id }})"
                                                wire:confirm="Activate this BOM? Other active BOMs for this product will be deactivated.">
                                            <i class="ri-check-line"></i>
                                        </button>
                                        @endcan
                                    @endif
                                    @can('bom.create')
                                    <button class="btn btn-sm btn-outline-primary btn-wave"
                                            wire:click="duplicateBom({{ $bom->id }})"
                                            title="Duplicate">
                                        <i class="ri-file-copy-line"></i>
                                    </button>
                                    @endcan
                                    @if($bom->status === 'draft')
                                        @can('bom.delete')
                                        <button class="btn btn-sm btn-outline-danger btn-wave"
                                                wire:click="deleteBom({{ $bom->id }})"
                                                wire:confirm="Delete this BOM?">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No BOMs found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($boms->hasPages())
        <div class="card-footer">
            {{ $boms->links() }}
        </div>
        @endif
    </div>

    <livewire:manufacturing.bom-form-modal />
</div>
