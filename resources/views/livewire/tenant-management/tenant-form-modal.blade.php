<div>
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $tenantId ? 'Edit Tenant' : 'Create Tenant' }}</h5>
                    <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        <div class="row gy-3">
                            <div class="col-12">
                                <label for="tenantName" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text"
                                       id="tenantName"
                                       wire:model.live="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       data-bs-toggle="tooltip"
                                       title="Organization or company name">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="tenantSlug" class="form-label">Slug <span class="text-danger">*</span></label>
                                <input type="text"
                                       id="tenantSlug"
                                       wire:model="slug"
                                       class="form-control @error('slug') is-invalid @enderror"
                                       data-bs-toggle="tooltip"
                                       title="URL-friendly identifier (auto-generated)">
                                @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="tenantDomain" class="form-label">Domain</label>
                                <input type="text"
                                       id="tenantDomain"
                                       wire:model="domain"
                                       class="form-control @error('domain') is-invalid @enderror"
                                       placeholder="e.g. company.example.com"
                                       data-bs-toggle="tooltip"
                                       title="Optional custom domain for this tenant">
                                @error('domain') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label for="tenantPlan" class="form-label">Plan <span class="text-danger">*</span></label>
                                <select id="tenantPlan"
                                        wire:model="plan"
                                        class="form-select @error('plan') is-invalid @enderror"
                                        data-bs-toggle="tooltip"
                                        title="Subscription plan for this tenant">
                                    <option value="starter">Starter</option>
                                    <option value="pro">Pro</option>
                                    <option value="enterprise">Enterprise</option>
                                </select>
                                @error('plan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-wave">
                            <span wire:loading.remove wire:target="save">{{ $tenantId ? 'Update' : 'Create' }}</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
