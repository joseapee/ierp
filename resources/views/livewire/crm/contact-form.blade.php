<div>
    @section('title', $contactId ? 'Edit Contact' : 'New Contact')

    <x-page-header :title="$contactId ? 'Edit Contact' : 'New Contact'" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Contacts', 'route' => 'crm.contacts.index'],
        ['label' => $contactId ? 'Edit' : 'New'],
    ]" />

    <div class="card custom-card">
        <div class="card-header"><div class="card-title">Contact Details</div></div>
        <div class="card-body">
            <div class="row gy-3">
                <div class="col-md-4">
                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" wire:model="first_name" class="form-control @error('first_name') is-invalid @enderror">
                    @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" wire:model="last_name" class="form-control @error('last_name') is-invalid @enderror">
                    @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Company</label>
                    <select wire:model="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                        <option value="">No Company</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                    @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                    <label class="form-label">Job Title</label>
                    <input type="text" wire:model="job_title" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Department</label>
                    <input type="text" wire:model="department" class="form-control">
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch mt-4">
                        <input type="checkbox" class="form-check-input" wire:model="is_primary" id="isPrimary">
                        <label class="form-check-label" for="isPrimary">Primary Contact</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea wire:model="notes" class="form-control" rows="3"></textarea>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('crm.contacts.index') }}" class="btn btn-light" wire:navigate>Cancel</a>
            <button type="button" class="btn btn-primary btn-wave" wire:click="save" wire:loading.attr="disabled">
                <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                {{ $contactId ? 'Update Contact' : 'Create Contact' }}
            </button>
        </div>
    </div>
</div>
