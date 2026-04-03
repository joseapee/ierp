<div>
    @section('title', 'Plans')

    <x-page-header title="Plan Management" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Plans'],
    ]">
        <x-slot:actions>
            <button class="btn btn-primary btn-wave"
                    wire:click="openCreate"
                    data-bs-toggle="tooltip"
                    title="Create a new plan">
                <i class="ri-add-line me-1"></i> Add Plan
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
                       placeholder="Search plans...">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Monthly</th>
                            <th>Annual</th>
                            <th>Trial Days</th>
                            <th>Subscribers</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($plans as $plan)
                        <tr wire:key="plan-{{ $plan->id }}">
                            <td>{{ $plan->sort_order }}</td>
                            <td>
                                <span class="fw-semibold">{{ $plan->name }}</span>
                            </td>
                            <td class="text-muted fs-12">{{ $plan->slug }}</td>
                            <td>{{ format_currency((float) $plan->monthly_price) }}</td>
                            <td>{{ format_currency((float) $plan->annual_price) }}</td>
                            <td>{{ $plan->trial_days }}</td>
                            <td>
                                <span class="badge bg-primary-transparent">{{ $plan->subscriptions_count ?? 0 }}</span>
                            </td>
                            <td>
                                @if($plan->is_active)
                                    <span class="badge bg-success-transparent">Active</span>
                                @else
                                    <span class="badge bg-danger-transparent">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-info btn-wave"
                                        wire:click="openEdit({{ $plan->id }})"
                                        data-bs-toggle="tooltip" title="Edit plan">
                                    <i class="ri-edit-line"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No plans found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($plans->hasPages())
        <div class="card-footer">
            {{ $plans->links() }}
        </div>
        @endif
    </div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">{{ $editingPlanId ? 'Edit Plan' : 'Create Plan' }}</h6>
                    <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Slug</label>
                            <input type="text" wire:model="slug" class="form-control @error('slug') is-invalid @enderror">
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea wire:model="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Monthly Price</label>
                            <input type="number" step="0.01" wire:model="monthlyPrice" class="form-control @error('monthlyPrice') is-invalid @enderror">
                            @error('monthlyPrice') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Annual Price</label>
                            <input type="number" step="0.01" wire:model="annualPrice" class="form-control @error('annualPrice') is-invalid @enderror">
                            @error('annualPrice') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Trial Days</label>
                            <input type="number" wire:model="trialDays" class="form-control @error('trialDays') is-invalid @enderror">
                            @error('trialDays') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sort Order</label>
                            <input type="number" wire:model="sortOrder" class="form-control">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input type="checkbox" wire:model="isActive" class="form-check-input" id="isActiveCheck">
                                <label class="form-check-label" for="isActiveCheck">Active</label>
                            </div>
                        </div>

                        {{-- Features --}}
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label mb-0">Features</label>
                                <button type="button" class="btn btn-sm btn-outline-primary btn-wave" wire:click="addFeature">
                                    <i class="ri-add-line me-1"></i> Add Feature
                                </button>
                            </div>
                            @foreach($features as $index => $feature)
                                <div class="row g-2 mb-2" wire:key="feature-{{ $index }}">
                                    <div class="col-5">
                                        <input type="text"
                                               wire:model="features.{{ $index }}.key"
                                               class="form-control form-control-sm"
                                               placeholder="Feature key (e.g. max_users)">
                                    </div>
                                    <div class="col-5">
                                        <input type="text"
                                               wire:model="features.{{ $index }}.value"
                                               class="form-control form-control-sm"
                                               placeholder="Value (e.g. 10, true, unlimited)">
                                    </div>
                                    <div class="col-2">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger btn-wave"
                                                wire:click="removeFeature({{ $index }})">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="$set('showModal', false)">Cancel</button>
                    <button type="button" class="btn btn-primary btn-wave" wire:click="save">
                        {{ $editingPlanId ? 'Update Plan' : 'Create Plan' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
