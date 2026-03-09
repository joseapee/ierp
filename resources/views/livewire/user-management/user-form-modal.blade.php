<div>
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
                            <div class="col-md-6">
                                <label for="userPassword" class="form-label">Password @if(!$userId)<span class="text-danger">*</span>@endif</label>
                                <input type="password"
                                       id="userPassword"
                                       wire:model="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="{{ $userId ? 'Leave blank to keep current' : '' }}"
                                       data-bs-toggle="tooltip"
                                       title="{{ $userId ? 'Leave blank to keep current password' : 'Minimum 8 characters' }}">
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
</div>
