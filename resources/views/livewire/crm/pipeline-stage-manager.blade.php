<div>
    @section('title', 'Pipeline Stages')

    <x-page-header title="Pipeline Stages" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Pipeline Stages'],
    ]">
        <x-slot:actions>
            <button class="btn btn-primary btn-wave" wire:click="openModal">
                <i class="ri-add-line me-1"></i> New Stage
            </button>
        </x-slot:actions>
    </x-page-header>

    <div class="card custom-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Color</th>
                            <th>Name</th>
                            <th class="text-end">Win Probability</th>
                            <th>Won</th>
                            <th>Lost</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stages as $stage)
                        <tr wire:key="stage-{{ $stage->id }}">
                            <td>{{ $stage->display_order }}</td>
                            <td>
                                <span class="d-inline-block rounded-circle" style="width:16px;height:16px;background:{{ $stage->color }}"></span>
                            </td>
                            <td class="fw-medium">{{ $stage->name }}</td>
                            <td class="text-end">{{ number_format((float)$stage->win_probability, 0) }}%</td>
                            <td>
                                @if($stage->is_won) <span class="badge bg-success-transparent">Yes</span> @endif
                            </td>
                            <td>
                                @if($stage->is_lost) <span class="badge bg-danger-transparent">Yes</span> @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $stage->is_active ? 'success' : 'danger' }}-transparent cursor-pointer"
                                      wire:click="toggleActive({{ $stage->id }})" style="cursor:pointer">
                                    {{ $stage->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary btn-wave" wire:click="openModal({{ $stage->id }})">
                                    <i class="ri-pencil-line"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger btn-wave" wire:click="deleteStage({{ $stage->id }})"
                                        wire:confirm="Are you sure you want to delete this stage?">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No pipeline stages found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color:rgba(0,0,0,.5)">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">{{ $editingId ? 'Edit Stage' : 'New Stage' }}</h6>
                    <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="row gy-3">
                        <div class="col-12">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Display Order <span class="text-danger">*</span></label>
                            <input type="number" wire:model="display_order" class="form-control @error('display_order') is-invalid @enderror">
                            @error('display_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Win Probability (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" max="100" wire:model="win_probability" class="form-control @error('win_probability') is-invalid @enderror">
                            @error('win_probability') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Color <span class="text-danger">*</span></label>
                            <input type="color" wire:model="color" class="form-control form-control-color" style="width:60px">
                            @error('color') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input type="checkbox" class="form-check-input" wire:model="is_active" id="stageActive">
                                <label class="form-check-label" for="stageActive">Active</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" wire:model="is_won" id="stageWon">
                                <label class="form-check-label" for="stageWon">Is Won Stage</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" wire:model="is_lost" id="stageLost">
                                <label class="form-check-label" for="stageLost">Is Lost Stage</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="$set('showModal', false)">Cancel</button>
                    <button type="button" class="btn btn-primary btn-wave" wire:click="save" wire:loading.attr="disabled">
                        <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                        {{ $editingId ? 'Update Stage' : 'Create Stage' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
