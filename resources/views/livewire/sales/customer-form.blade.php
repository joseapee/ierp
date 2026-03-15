<div>
    @section('title', $customerId ? 'Edit Customer' : 'New Customer')

    <x-page-header :title="$customerId ? 'Edit Customer' : 'New Customer'" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Customers', 'route' => 'sales.customers.index'],
        ['label' => $customerId ? 'Edit' : 'New'],
    ]" />

    <div class="card custom-card">
        <div class="card-header"><div class="card-title">Customer Details</div></div>
        <div class="card-body">
            <div class="row gy-3">
                <div class="col-md-6">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Email</label>
                    <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Phone</label>
                    <input type="text" wire:model="phone" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tax ID</label>
                    <input type="text" wire:model="tax_id" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Credit Limit <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" wire:model="credit_limit" class="form-control @error('credit_limit') is-invalid @enderror">
                    @error('credit_limit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Payment Terms (days) <span class="text-danger">*</span></label>
                    <input type="number" wire:model="payment_terms" class="form-control @error('payment_terms') is-invalid @enderror">
                    @error('payment_terms') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Currency</label>
                    <input type="text" wire:model="currency_code" class="form-control" maxlength="3">
                </div>
            </div>
        </div>
    </div>

    <div class="card custom-card">
        <div class="card-header"><div class="card-title">Billing Address</div></div>
        <div class="card-body">
            <div class="row gy-3">
                <div class="col-md-6">
                    <label class="form-label">Address Line 1</label>
                    <input type="text" wire:model="billing_address_line1" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Address Line 2</label>
                    <input type="text" wire:model="billing_address_line2" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">City</label>
                    <input type="text" wire:model="billing_city" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">State</label>
                    <input type="text" wire:model="billing_state" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Postal Code</label>
                    <input type="text" wire:model="billing_postal_code" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Country</label>
                    <input type="text" wire:model="billing_country" class="form-control">
                </div>
            </div>
        </div>
    </div>

    <div class="card custom-card">
        <div class="card-header"><div class="card-title">Shipping Address</div></div>
        <div class="card-body">
            <div class="row gy-3">
                <div class="col-md-6">
                    <label class="form-label">Address Line 1</label>
                    <input type="text" wire:model="shipping_address_line1" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Address Line 2</label>
                    <input type="text" wire:model="shipping_address_line2" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">City</label>
                    <input type="text" wire:model="shipping_city" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">State</label>
                    <input type="text" wire:model="shipping_state" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Postal Code</label>
                    <input type="text" wire:model="shipping_postal_code" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Country</label>
                    <input type="text" wire:model="shipping_country" class="form-control">
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
                        <input type="checkbox" class="form-check-input" wire:model="is_active" id="customerActive">
                        <label class="form-check-label" for="customerActive">Active</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('sales.customers.index') }}" class="btn btn-light" wire:navigate>Cancel</a>
            <button type="button" class="btn btn-primary btn-wave" wire:click="save" wire:loading.attr="disabled">
                <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                {{ $customerId ? 'Update Customer' : 'Create Customer' }}
            </button>
        </div>
    </div>
</div>
