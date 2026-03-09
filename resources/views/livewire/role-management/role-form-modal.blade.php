<div>
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $roleId ? 'Edit Role' : 'Create Role' }}</h5>
                    <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        <div class="row gy-3">
                            <div class="col-md-6">
                                <label for="roleName" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text"
                                       id="roleName"
                                       wire:model.live="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       data-bs-toggle="tooltip"
                                       title="Display name for this role">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="roleSlug" class="form-label">Slug <span class="text-danger">*</span></label>
                                <input type="text"
                                       id="roleSlug"
                                       wire:model="slug"
                                       class="form-control @error('slug') is-invalid @enderror"
                                       data-bs-toggle="tooltip"
                                       title="Unique identifier (auto-generated from name)">
                                @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label for="roleDescription" class="form-label">Description</label>
                                <textarea id="roleDescription"
                                          wire:model="description"
                                          class="form-control @error('description') is-invalid @enderror"
                                          rows="2"
                                          data-bs-toggle="tooltip"
                                          title="Optional description of this role"></textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Permissions</label>
                                @foreach($permissionsByModule as $module => $permissions)
                                <div class="mb-3">
                                    <h6 class="fw-semibold text-primary text-capitalize mb-2">{{ $module }}</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($permissions as $permission)
                                        <div class="form-check">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   id="perm_{{ $permission->id }}"
                                                   value="{{ $permission->id }}"
                                                   wire:model="permission_ids"
                                                   data-bs-toggle="tooltip"
                                                   title="{{ $permission->description ?? $permission->name }}">
                                            <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                {{ $permission->action }}
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-wave">
                            <span wire:loading.remove wire:target="save">{{ $roleId ? 'Update' : 'Create' }}</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
