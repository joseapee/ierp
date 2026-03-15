<div>
    {{-- Communication Timeline --}}
    <div class="card custom-card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div class="card-title mb-0">Communications</div>
            @can('crm-communications.create')
            <button class="btn btn-sm btn-primary btn-wave" wire:click="openModal">
                <i class="ri-add-line me-1"></i> Log Communication
            </button>
            @endcan
        </div>
        <div class="card-body">
            @forelse($communications as $comm)
            <div class="d-flex mb-3" wire:key="comm-{{ $comm->id }}">
                <div class="me-3">
                    @php
                        $typeIcons = ['email' => 'ri-mail-line', 'phone' => 'ri-phone-line', 'meeting' => 'ri-calendar-event-line', 'note' => 'ri-sticky-note-line'];
                    @endphp
                    <span class="avatar avatar-sm bg-primary-transparent">
                        <i class="{{ $typeIcons[$comm->type] ?? 'ri-chat-3-line' }}"></i>
                    </span>
                </div>
                <div class="flex-fill">
                    <div class="d-flex justify-content-between">
                        <span class="fw-medium">{{ $comm->subject }}</span>
                        <small class="text-muted">{{ $comm->created_at->diffForHumans() }}</small>
                    </div>
                    <div class="text-muted small">
                        <span class="badge bg-secondary-transparent me-1">{{ ucfirst($comm->type) }}</span>
                        @if($comm->contact)
                            <span>{{ $comm->contact->full_name }}</span>
                        @endif
                        @if($comm->creator)
                            <span class="ms-2">by {{ $comm->creator->name }}</span>
                        @endif
                    </div>
                    @if($comm->message)
                    <p class="text-muted small mt-1 mb-0">{{ Str::limit($comm->message, 200) }}</p>
                    @endif
                </div>
            </div>
            @empty
            <p class="text-muted text-center mb-0">No communications logged yet.</p>
            @endforelse
        </div>
    </div>

    {{-- Log Communication Modal --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color:rgba(0,0,0,.5)">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Log Communication</h6>
                    <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="row gy-3">
                        <div class="col-md-6">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select wire:model="type" class="form-select">
                                <option value="email">Email</option>
                                <option value="phone">Phone</option>
                                <option value="meeting">Meeting</option>
                                <option value="note">Note</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" wire:model="subject" class="form-control @error('subject') is-invalid @enderror">
                            @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea wire:model="message" class="form-control @error('message') is-invalid @enderror" rows="4"></textarea>
                            @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="$set('showModal', false)">Cancel</button>
                    <button type="button" class="btn btn-primary btn-wave" wire:click="save" wire:loading.attr="disabled">
                        <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                        Log Communication
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
