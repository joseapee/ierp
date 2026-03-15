<div>
    @section('title', 'Chart of Accounts')

    <x-page-header title="Chart of Accounts" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Chart of Accounts'],
    ]">
        <x-slot:actions>
            <button type="button" class="btn btn-primary btn-wave" wire:click="openCreateModal">
                <i class="ri-add-line me-1"></i> Add Account
            </button>
        </x-slot:actions>
    </x-page-header>

    <div class="card custom-card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex gap-2 flex-wrap">
                <input type="text" wire:model.live.debounce.300ms="search"
                       class="form-control form-control-sm" style="width:220px"
                       placeholder="Search code or name...">
                <select wire:model.live="typeFilter" class="form-select form-select-sm" style="width:160px">
                    <option value="">All Types</option>
                    <option value="asset">Asset</option>
                    <option value="liability">Liability</option>
                    <option value="equity">Equity</option>
                    <option value="revenue">Revenue</option>
                    <option value="expense">Expense</option>
                </select>
                <select wire:model.live="statusFilter" class="form-select form-select-sm" style="width:140px">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <span class="text-muted fs-12">{{ $accounts->count() }} accounts</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Account Name</th>
                            <th>Type</th>
                            <th>Sub-Type</th>
                            <th>Normal Balance</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $account)
                        <tr wire:key="account-{{ $account->id }}">
                            <td><code>{{ $account->code }}</code></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($account->parent_id)
                                        <span class="text-muted">└</span>
                                    @endif
                                    <span class="fw-medium">{{ $account->name }}</span>
                                    @if($account->is_system)
                                        <span class="badge bg-secondary-transparent fs-9">System</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @php
                                    $typeColors = ['asset' => 'primary', 'liability' => 'warning', 'equity' => 'info', 'revenue' => 'success', 'expense' => 'danger'];
                                @endphp
                                <span class="badge bg-{{ $typeColors[$account->type] ?? 'secondary' }}-transparent">{{ ucfirst($account->type) }}</span>
                            </td>
                            <td>{{ $account->sub_type ? ucwords(str_replace('_', ' ', $account->sub_type)) : '—' }}</td>
                            <td>{{ ucfirst($account->normal_balance) }}</td>
                            <td>
                                @if($account->is_active)
                                    <span class="badge bg-success-transparent">Active</span>
                                @else
                                    <span class="badge bg-danger-transparent">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if(!$account->is_system)
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-sm btn-outline-info btn-wave"
                                            wire:click="openEditModal({{ $account->id }})">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger btn-wave"
                                            wire:click="deleteAccount({{ $account->id }})"
                                            wire:confirm="Are you sure you want to delete this account?">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                                @else
                                    <span class="text-muted fs-11">Protected</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No accounts found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Create/Edit Account Modal --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color:rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">{{ $editingAccountId ? 'Edit Account' : 'New Account' }}</h6>
                    <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                </div>
                <form wire:submit="save">
                    <div class="modal-body">
                        <div class="row gy-3">
                            <div class="col-md-4">
                                <label class="form-label">Code <span class="text-danger">*</span>
                                    <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Unique account code (e.g. 1000, 2100). Used for sorting and identification."></i>
                                </label>
                                <input type="text" wire:model="code" class="form-control @error('code') is-invalid @enderror" placeholder="e.g. 1000">
                                @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Name <span class="text-danger">*</span>
                                    <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Descriptive name for this account (e.g. Cash, Accounts Receivable)."></i>
                                </label>
                                <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Type <span class="text-danger">*</span>
                                    <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Account classification: Asset, Liability, Equity, Revenue, or Expense."></i>
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
                                    <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Optional sub-category for grouping in financial reports (e.g. current_asset, fixed_asset, cogs)."></i>
                                </label>
                                <input type="text" wire:model="sub_type" class="form-control" placeholder="e.g. current_asset">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Normal Balance <span class="text-danger">*</span>
                                    <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="The side that increases this account. Assets and Expenses normally have debit balances."></i>
                                </label>
                                <select wire:model="normal_balance" class="form-select @error('normal_balance') is-invalid @enderror">
                                    <option value="debit">Debit</option>
                                    <option value="credit">Credit</option>
                                </select>
                                @error('normal_balance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Parent Account
                                    <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Optional parent for hierarchical grouping in the chart of accounts."></i>
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
                                    <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Internal notes about this account's purpose."></i>
                                </label>
                                <input type="text" wire:model="description" class="form-control">
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" wire:model="is_active" id="accountActive">
                                    <label class="form-check-label" for="accountActive">Active
                                        <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Inactive accounts are hidden from transaction forms but retained for historical records."></i>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="$set('showModal', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-wave">
                            <span wire:loading.remove wire:target="save">{{ $editingAccountId ? 'Update' : 'Create' }}</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
