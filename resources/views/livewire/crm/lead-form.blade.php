<div>
    @section('title', $leadId ? 'Edit Lead' : 'New Lead')

    <x-page-header :title="$leadId ? 'Edit Lead' : 'New Lead'" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Leads', 'route' => 'crm.leads.index'],
        ['label' => $leadId ? 'Edit' : 'New'],
    ]" />

    <div class="card custom-card">
        <div class="card-header"><div class="card-title">Lead Details</div></div>
        <div class="card-body">
            <div class="row gy-3">
                <div class="col-md-6">
                    <label class="form-label">Lead Name <span class="text-danger">*</span></label>
                    <input type="text" wire:model="lead_name" class="form-control @error('lead_name') is-invalid @enderror">
                    @error('lead_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Company Name</label>
                    <input type="text" wire:model="company_name" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phone</label>
                    <input type="text" wire:model="phone" class="form-control @error('phone') is-invalid @enderror">
                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Industry</label>
                    <input type="text" wire:model="industry" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Source <span class="text-danger">*</span></label>
                    <select wire:model="source" class="form-select @error('source') is-invalid @enderror">
                        <option value="manual">Manual</option>
                        <option value="website">Website</option>
                        <option value="social_media">Social Media</option>
                        <option value="email_campaign">Email Campaign</option>
                        <option value="phone_inquiry">Phone Inquiry</option>
                        <option value="walk_in">Walk-in</option>
                        <option value="referral">Referral</option>
                    </select>
                    @error('source') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estimated Value <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" wire:model="estimated_value" class="form-control @error('estimated_value') is-invalid @enderror">
                    @error('estimated_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Lead Score <span class="text-danger">*</span></label>
                    <input type="number" wire:model="lead_score" class="form-control @error('lead_score') is-invalid @enderror">
                    @error('lead_score') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Assigned To</label>
                    <select wire:model="assigned_to" class="form-select">
                        <option value="">Unassigned</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea wire:model="notes" class="form-control" rows="3"></textarea>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('crm.leads.index') }}" class="btn btn-light" wire:navigate>Cancel</a>
            <button type="button" class="btn btn-primary btn-wave" wire:click="save" wire:loading.attr="disabled">
                <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                {{ $leadId ? 'Update Lead' : 'Create Lead' }}
            </button>
        </div>
    </div>
</div>
