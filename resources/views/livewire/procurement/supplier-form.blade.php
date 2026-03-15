<div>
    @section('title', $supplierId ? 'Edit Supplier' : 'New Supplier')

    <x-page-header :title="$supplierId ? 'Edit Supplier' : 'New Supplier'" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Suppliers', 'route' => 'procurement.suppliers.index'],
        ['label' => $supplierId ? 'Edit' : 'New'],
    ]" />

    <div class="card custom-card">
        <div class="card-header"><div class="card-title">Supplier Details</div></div>
        <div class="card-body">
            <div class="row gy-3">
                <div class="col-md-6">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact Person</label>
                    <input type="text" wire:model="contact_person" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phone</label>
                    <input type="text" wire:model="phone" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tax ID</label>
                    <input type="text" wire:model="tax_id" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Payment Terms (days) <span class="text-danger">*</span></label>
                    <input type="number" wire:model="payment_terms" class="form-control @error('payment_terms') is-invalid @enderror">
                    @error('payment_terms') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Lead Time (days)</label>
                    <input type="number" wire:model="lead_time_days" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Currency</label>
                    <input type="text" wire:model="currency_code" class="form-control" maxlength="3">
                </div>
            </div>
        </div>
    </div>

    <div class="card custom-card">
        <div class="card-header"><div class="card-title">Address</div></div>
        <div class="card-body">
            <div class="row gy-3">
                <div class="col-md-6">
                    <label class="form-label">Address Line 1</label>
                    <input type="text" wire:model="address_line1" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Address Line 2</label>
                    <input type="text" wire:model="address_line2" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">City</label>
                    <input type="text" wire:model="city" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">State</label>
                    <input type="text" wire:model="state" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Postal Code</label>
                    <input type="text" wire:model="postal_code" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Country</label>
                    <input type="text" wire:model="country" class="form-control">
                </div>
            </div>
        </div>
    </div>

    <div class="card custom-card">
        <div class="card-body">
            <div class="row gy-3">
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea wire:model="notes" class="form-control" rows="3"></textarea>
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" wire:model="is_active" id="supplierActive">
                        <label class="form-check-label" for="supplierActive">Active</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('procurement.suppliers.index') }}" class="btn btn-light" wire:navigate>Cancel</a>
            <button type="button" class="btn btn-primary btn-wave" wire:click="save" wire:loading.attr="disabled">
                <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                {{ $supplierId ? 'Update Supplier' : 'Create Supplier' }}
            </button>
        </div>
    </div>
</div>
