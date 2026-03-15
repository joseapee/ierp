<div>
    @section('title', $opportunityId ? 'Edit Opportunity' : 'New Opportunity')

    <x-page-header :title="$opportunityId ? 'Edit Opportunity' : 'New Opportunity'" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Opportunities', 'route' => 'crm.opportunities.index'],
        ['label' => $opportunityId ? 'Edit' : 'New'],
    ]" />

    <div class="card custom-card">
        <div class="card-header"><div class="card-title">Opportunity Details</div></div>
        <div class="card-body">
            <div class="row gy-3">
                <div class="col-md-6">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Customer <span class="text-danger">*</span></label>
                    <select wire:model="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                        <option value="">Select Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                    @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Contact</label>
                    <select wire:model="contact_id" class="form-select @error('contact_id') is-invalid @enderror">
                        <option value="">No Contact</option>
                        @foreach($contacts as $contact)
                            <option value="{{ $contact->id }}">{{ $contact->full_name }}</option>
                        @endforeach
                    </select>
                    @error('contact_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pipeline Stage <span class="text-danger">*</span></label>
                    <select wire:model="pipeline_stage_id" class="form-select @error('pipeline_stage_id') is-invalid @enderror">
                        <option value="">Select Stage</option>
                        @foreach($stages as $stage)
                            <option value="{{ $stage->id }}">{{ $stage->name }} ({{ $stage->win_probability }}%)</option>
                        @endforeach
                    </select>
                    @error('pipeline_stage_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Assigned To</label>
                    <select wire:model="assigned_to" class="form-select">
                        <option value="">Unassigned</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Expected Value <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" wire:model="expected_value" class="form-control @error('expected_value') is-invalid @enderror">
                    @error('expected_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Probability (%) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" max="100" wire:model="probability" class="form-control @error('probability') is-invalid @enderror">
                    @error('probability') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Expected Close Date</label>
                    <input type="date" wire:model="expected_close_date" class="form-control @error('expected_close_date') is-invalid @enderror">
                    @error('expected_close_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea wire:model="notes" class="form-control" rows="3"></textarea>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('crm.opportunities.index') }}" class="btn btn-light" wire:navigate>Cancel</a>
            <button type="button" class="btn btn-primary btn-wave" wire:click="save" wire:loading.attr="disabled">
                <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                {{ $opportunityId ? 'Update Opportunity' : 'Create Opportunity' }}
            </button>
        </div>
    </div>
</div>
