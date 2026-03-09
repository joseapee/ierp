<div>
    @if($showModal && $user)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Details</h5>
                    <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <span class="avatar avatar-lg avatar-rounded bg-primary-transparent fs-20">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </span>
                        <div>
                            <h5 class="mb-0">{{ $user->name }}</h5>
                            <span class="text-muted fs-13">{{ $user->email }}</span>
                        </div>
                    </div>
                    <div class="row gy-3">
                        <div class="col-6">
                            <span class="text-muted d-block fs-12">Phone</span>
                            <span>{{ $user->phone ?: '—' }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block fs-12">Status</span>
                            @if($user->is_active)
                                <span class="badge bg-success-transparent">Active</span>
                            @else
                                <span class="badge bg-danger-transparent">Inactive</span>
                            @endif
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block fs-12">Tenant</span>
                            <span>{{ $user->tenant?->name ?: 'Super Admin' }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block fs-12">Last Login</span>
                            <span>{{ $user->last_login_at?->diffForHumans() ?: 'Never' }}</span>
                        </div>
                        <div class="col-12">
                            <span class="text-muted d-block fs-12 mb-1">Roles</span>
                            @forelse($user->roles as $role)
                                <span class="badge bg-primary-transparent me-1">{{ $role->name }}</span>
                            @empty
                                <span class="text-muted">No roles assigned</span>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
