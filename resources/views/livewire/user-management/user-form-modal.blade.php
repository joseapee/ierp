<div>
    {{-- ═══════════ USER FORM MODAL ═══════════ --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $userId ? 'Edit User' : 'Create User' }}</h5>
                    <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        <div class="row gy-3">
                            <div class="col-md-6">
                                <label for="userName" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text"
                                       id="userName"
                                       wire:model="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       data-bs-toggle="tooltip"
                                       title="Full name of the user">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="userEmail" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email"
                                       id="userEmail"
                                       wire:model="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       data-bs-toggle="tooltip"
                                       title="Unique email address for login">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6" x-data="{ showPassword: false }">
                                <label for="userPassword" class="form-label">Password @if(!$userId)<span class="text-danger">*</span>@endif</label>
                                <div class="input-group">
                                    <input :type="showPassword ? 'text' : 'password'"
                                           id="userPassword"
                                           wire:model="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           placeholder="{{ $userId ? 'Leave blank to keep current' : '' }}"
                                           data-bs-toggle="tooltip"
                                           title="{{ $userId ? 'Leave blank to keep current password' : 'Minimum 8 characters' }}">
                                    <button type="button"
                                            class="btn btn-outline-secondary"
                                            @click="showPassword = !showPassword"
                                            data-bs-toggle="tooltip"
                                            title="Toggle password visibility">
                                        <i :class="showPassword ? 'ri-eye-off-line' : 'ri-eye-line'"></i>
                                    </button>
                                    <button type="button"
                                            class="btn btn-outline-secondary"
                                            x-on:click="
                                                const input = document.getElementById('userPassword');
                                                if (input.value) {
                                                    navigator.clipboard.writeText(input.value);
                                                    Livewire.dispatch('toast', { message: 'Password copied!', type: 'info' });
                                                }
                                            "
                                            data-bs-toggle="tooltip"
                                            title="Copy password">
                                        <i class="ri-file-copy-line"></i>
                                    </button>
                                    @if(!$userId)
                                    <button type="button"
                                            class="btn btn-outline-primary"
                                            wire:click="generatePassword"
                                            data-bs-toggle="tooltip"
                                            title="Generate a strong random password">
                                        <i class="ri-refresh-line"></i>
                                    </button>
                                    @endif
                                </div>
                                @error('password') <div class="text-danger fs-12 mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="userPasswordConfirmation" class="form-label">Confirm Password</label>
                                <input type="password"
                                       id="userPasswordConfirmation"
                                       wire:model="password_confirmation"
                                       class="form-control"
                                       data-bs-toggle="tooltip"
                                       title="Re-enter password to confirm">
                            </div>
                            <div class="col-md-6">
                                <label for="userPhone" class="form-label">Phone</label>
                                <input type="text"
                                       id="userPhone"
                                       wire:model="phone"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       data-bs-toggle="tooltip"
                                       title="Optional contact phone number">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           id="userIsActive"
                                           wire:model="is_active"
                                           data-bs-toggle="tooltip"
                                           title="Toggle user active state">
                                    <label class="form-check-label" for="userIsActive">Active</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Roles</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($roles as $role)
                                    <div class="form-check">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               id="role_{{ $role->id }}"
                                               value="{{ $role->id }}"
                                               wire:model="role_ids"
                                               data-bs-toggle="tooltip"
                                               title="{{ $role->description ?? $role->name }}">
                                        <label class="form-check-label" for="role_{{ $role->id }}">{{ $role->name }}</label>
                                    </div>
                                    @endforeach
                                </div>
                                @error('role_ids') <div class="text-danger fs-12 mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-wave">
                            <span wire:loading.remove wire:target="save">{{ $userId ? 'Update' : 'Create' }}</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════ CREDENTIALS SUCCESS MODAL ═══════════ --}}
    @if($showCredentialsModal && $createdCredentials)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success-transparent">
                    <h5 class="modal-title">
                        <i class="ri-check-double-line me-1 text-success"></i> User Created Successfully
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeCredentialsModal"></button>
                </div>
                <div class="modal-body" x-data>
                    <div class="text-center mb-4">
                        <span class="avatar avatar-xl avatar-rounded bg-success-transparent mb-3">
                            <i class="ri-user-add-line fs-30 text-success"></i>
                        </span>
                        <h5 class="fw-semibold mb-1">{{ $createdCredentials['name'] }}</h5>
                        <span class="text-muted fs-13">Account ready to use</span>
                    </div>

                    <div class="card custom-card bg-light mb-3">
                        <div class="card-body py-3">
                            <h6 class="fw-semibold mb-3 fs-13">
                                <i class="ri-key-line me-1"></i> Login Credentials
                            </h6>
                            <div class="row gy-2">
                                <div class="col-12">
                                    <span class="text-muted fs-12 d-block">Email</span>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="fw-medium" id="credEmail">{{ $createdCredentials['email'] }}</span>
                                        <button type="button"
                                                class="btn btn-sm btn-icon btn-outline-primary"
                                                x-on:click="
                                                    navigator.clipboard.writeText('{{ $createdCredentials['email'] }}');
                                                    Livewire.dispatch('toast', { message: 'Email copied!', type: 'info' });
                                                "
                                                data-bs-toggle="tooltip" title="Copy email">
                                            <i class="ri-file-copy-line"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <span class="text-muted fs-12 d-block">Password</span>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <code class="fs-14" id="credPassword">{{ $createdCredentials['password'] }}</code>
                                        <button type="button"
                                                class="btn btn-sm btn-icon btn-outline-primary"
                                                x-on:click="
                                                    navigator.clipboard.writeText('{{ $createdCredentials['password'] }}');
                                                    Livewire.dispatch('toast', { message: 'Password copied!', type: 'info' });
                                                "
                                                data-bs-toggle="tooltip" title="Copy password">
                                            <i class="ri-file-copy-line"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Copy all credentials --}}
                    <button type="button"
                            class="btn btn-outline-primary btn-wave w-100 mb-2"
                            x-on:click="
                                const text = 'Email: {{ $createdCredentials['email'] }}\nPassword: {{ $createdCredentials['password'] }}';
                                navigator.clipboard.writeText(text);
                                Livewire.dispatch('toast', { message: 'All credentials copied!', type: 'info' });
                            ">
                        <i class="ri-file-copy-line me-1"></i> Copy All Credentials
                    </button>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button"
                            class="btn btn-info btn-wave"
                            wire:click="emailCredentials"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="emailCredentials">
                            <i class="ri-mail-send-line me-1"></i> Email Credentials to User
                        </span>
                        <span wire:loading wire:target="emailCredentials">
                            <i class="ri-loader-4-line me-1 ri-spin"></i> Sending...
                        </span>
                    </button>
                    <button type="button" class="btn btn-secondary" wire:click="closeCredentialsModal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
