<div>
    @section('title', 'Production Stages')

    <x-page-header title="Production Stages" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Production Stages'],
    ]">
        <x-slot:actions>
            @can('production.manage')
                <button class="btn btn-primary btn-wave" wire:click="openModal">
                    <i class="ri-add-line me-1"></i> Add Stage
                </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card custom-card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex gap-2 flex-wrap">
                <select wire:model.live="industryFilter" class="form-select form-select-sm" style="width:180px">
                    <option value="">All Industries</option>
                    @foreach($industries as $industry)
                        <option value="{{ $industry }}">{{ ucfirst($industry) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Industry</th>
                            <th class="text-end">Sort Order</th>
                            <th class="text-end">Est. Duration</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stages as $stage)
                        <tr wire:key="stage-{{ $stage->id }}">
                            <td><span class="fw-medium">{{ $stage->name }}</span></td>
                            <td><code>{{ $stage->code ?? '—' }}</code></td>
                            <td><span class="badge bg-info-transparent">{{ ucfirst($stage->industry_type) }}</span></td>
                            <td class="text-end">{{ $stage->sort_order }}</td>
                            <td class="text-end">
                                @if($stage->estimated_duration_minutes)
                                    {{ $stage->estimated_duration_minutes }} min
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input" type="checkbox"
                                           {{ $stage->is_active ? 'checked' : '' }}
                                           wire:click="toggleActive({{ $stage->id }})">
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    @can('production.manage')
                                    <button class="btn btn-sm btn-outline-info btn-wave" wire:click="openModal({{ $stage->id }})">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger btn-wave"
                                            wire:click="deleteStage({{ $stage->id }})"
                                            wire:confirm="Delete this stage?">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No stages found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Stage Form Modal --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">{{ $editingId ? 'Edit Stage' : 'New Stage' }}</h6>
                    <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Cutting">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Code</label>
                            <input type="text" wire:model="code" class="form-control" placeholder="e.g. CUT">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Industry Type <span class="text-danger">*</span></label>
                            <select wire:model="industry_type" class="form-select">
                                <option value="general">General</option>
                                <option value="fashion">Fashion</option>
                                <option value="furniture">Furniture</option>
                                <option value="restaurant">Restaurant</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Sort Order</label>
                            <input type="number" wire:model="sort_order" class="form-control" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Est. Duration (min)</label>
                            <input type="number" wire:model="estimated_duration_minutes" class="form-control" min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea wire:model="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" wire:model="is_active" id="stageActive">
                        <label class="form-check-label" for="stageActive">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="$set('showModal', false)">Cancel</button>
                    <button type="button" class="btn btn-primary btn-wave" wire:click="save" wire:loading.attr="disabled">
                        <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                        {{ $editingId ? 'Update' : 'Create' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
