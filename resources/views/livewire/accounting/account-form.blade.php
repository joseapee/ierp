<div>
    @section('title', $accountId ? 'Edit Account' : 'New Account')

    <x-page-header :title="$accountId ? 'Edit Account' : 'New Account'" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Chart of Accounts', 'route' => 'accounting.accounts.index'],
        ['label' => $accountId ? 'Edit' : 'New'],
    ]" />

    <div class="card custom-card">
        <div class="card-header"><div class="card-title">Account Details</div></div>
        <div class="card-body">
            <div class="row gy-3">
                <div class="col-md-3">
                    <label class="form-label">Code <span class="text-danger">*</span>
                        <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Unique account code used for sorting and identification"></i>
                    </label>
                    <input type="text" wire:model="code" class="form-control @error('code') is-invalid @enderror" placeholder="e.g. 1000">
                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-9">
                    <label class="form-label">Name <span class="text-danger">*</span>
                        <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Descriptive name for this account"></i>
                    </label>
                    <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Type <span class="text-danger">*</span>
                        <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Account classification determines its role in financial reports"></i>
                    </label>
                    <select wire:model.live="type" class="form-select @error('type') is-invalid @enderror">
                        <option value="asset">Asset</option>
                        <option value="liability">Liability</option>
                        <option value="equity">Equity</option>
                        <option value="revenue">Revenue</option>
                        <option value="expense">Expense</option>
                    </select>
                    @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sub-Type
                        <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Optional sub-category for financial report grouping"></i>
                    </label>
                    <input type="text" wire:model="sub_type" class="form-control" placeholder="e.g. current_asset">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Normal Balance <span class="text-danger">*</span>
                        <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="The side that increases this account balance"></i>
                    </label>
                    <select wire:model="normal_balance" class="form-select @error('normal_balance') is-invalid @enderror">
                        <option value="debit">Debit</option>
                        <option value="credit">Credit</option>
                    </select>
                    @error('normal_balance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Parent Account
                        <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Optional parent for hierarchical grouping"></i>
                    </label>
                    <select wire:model="parent_id" class="form-select">
                        <option value="">— None (Top Level) —</option>
                        @foreach($parentAccounts as $pa)
                            <option value="{{ $pa->id }}">{{ $pa->code }} — {{ $pa->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Description
                        <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Internal notes about this account's purpose"></i>
                    </label>
                    <input type="text" wire:model="description" class="form-control">
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" wire:model="is_active" id="accountActive">
                        <label class="form-check-label" for="accountActive">Active</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('accounting.accounts.index') }}" class="btn btn-light" wire:navigate>Cancel</a>
            <button type="button" class="btn btn-primary btn-wave" wire:click="save" wire:loading.attr="disabled">
                <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                {{ $accountId ? 'Update Account' : 'Create Account' }}
            </button>
        </div>
    </div>
</div>
