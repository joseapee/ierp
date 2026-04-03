<div>
    @section('title', 'Lead: ' . $lead->lead_name)

    <x-page-header :title="'Lead: ' . $lead->lead_name" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Leads', 'route' => 'crm.leads.index'],
        ['label' => $lead->lead_name],
    ]">
        <x-slot:actions>
            @if(!in_array($lead->status, ['converted', 'lost']))
                @can('leads.convert')
                <button class="btn btn-success btn-wave" wire:click="openConvertModal">
                    <i class="ri-exchange-line me-1"></i> Convert
                </button>
                @endcan
                @can('leads.edit')
                <button class="btn btn-danger btn-wave" wire:click="openLostModal">
                    <i class="ri-close-circle-line me-1"></i> Mark Lost
                </button>
                @endcan
            @endif
            @can('leads.edit')
            <a href="{{ route('crm.leads.edit', $lead) }}" class="btn btn-outline-primary btn-wave" wire:navigate>
                <i class="ri-pencil-line me-1"></i> Edit
            </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    {{-- Lead Info --}}
    <div class="row">
        <div class="col-lg-8">
            <div class="card custom-card">
                <div class="card-header"><div class="card-title">Lead Information</div></div>
                <div class="card-body">
                    <div class="row gy-2">
                        <div class="col-md-4"><span class="text-muted">Name:</span><br><strong>{{ $lead->lead_name }}</strong></div>
                        <div class="col-md-4"><span class="text-muted">Company:</span><br><strong>{{ $lead->company_name ?? '—' }}</strong></div>
                        <div class="col-md-4">
                            <span class="text-muted">Status:</span><br>
                            @php
                                $statusColors = ['new' => 'info', 'contacted' => 'primary', 'qualified' => 'warning', 'proposal_sent' => 'secondary', 'negotiation' => 'dark', 'converted' => 'success', 'lost' => 'danger'];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$lead->status] ?? 'secondary' }}-transparent">
                                {{ str_replace('_', ' ', ucfirst($lead->status)) }}
                            </span>
                        </div>
                        <div class="col-md-4"><span class="text-muted">Email:</span><br>{{ $lead->email ?? '—' }}</div>
                        <div class="col-md-4"><span class="text-muted">Phone:</span><br>{{ $lead->phone ?? '—' }}</div>
                        <div class="col-md-4"><span class="text-muted">Source:</span><br>{{ str_replace('_', ' ', ucfirst($lead->source ?? '')) }}</div>
                        <div class="col-md-4"><span class="text-muted">Industry:</span><br>{{ $lead->industry ?? '—' }}</div>
                        <div class="col-md-4"><span class="text-muted">Assigned To:</span><br>{{ $lead->assignedUser?->name ?? '—' }}</div>
                        <div class="col-md-4"><span class="text-muted">Created:</span><br>{{ format_datetime($lead->created_at) }}</div>
                        @if($lead->notes)
                        <div class="col-12"><span class="text-muted">Notes:</span><br>{{ $lead->notes }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card custom-card">
                <div class="card-header"><div class="card-title">Value & Scoring</div></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Estimated Value:</span>
                        <span class="fw-bold">{{ format_currency((float)$lead->estimated_value) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Lead Score:</span>
                        <span class="fw-bold">{{ $lead->lead_score }}</span>
                    </div>
                    @if($lead->converted_at)
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Converted At:</span>
                        <span>{{ format_datetime($lead->converted_at) }}</span>
                    </div>
                    @if($lead->convertedCustomer)
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Customer:</span>
                        <span class="fw-medium">{{ $lead->convertedCustomer->name }}</span>
                    </div>
                    @endif
                    @endif
                </div>
            </div>

            {{-- Status Actions --}}
            @if(!in_array($lead->status, ['converted', 'lost']))
            <div class="card custom-card">
                <div class="card-header"><div class="card-title">Status Actions</div></div>
                <div class="card-body">
                    @php
                        $transitions = match($lead->status) {
                            'new' => ['contacted', 'qualified'],
                            'contacted' => ['qualified', 'proposal_sent'],
                            'qualified' => ['proposal_sent', 'negotiation'],
                            'proposal_sent' => ['negotiation'],
                            'negotiation' => [],
                            default => [],
                        };
                    @endphp
                    @can('leads.edit')
                    <div class="d-flex gap-2 flex-wrap">
                        @foreach($transitions as $nextStatus)
                        <button class="btn btn-sm btn-outline-primary btn-wave"
                                wire:click="updateStatus('{{ $nextStatus }}')"
                                wire:loading.attr="disabled">
                            {{ str_replace('_', ' ', ucfirst($nextStatus)) }}
                        </button>
                        @endforeach
                    </div>
                    @endcan
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Convert Modal --}}
    @if($showConvertModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color:rgba(0,0,0,.5)">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Convert Lead to Customer</h6>
                    <button type="button" class="btn-close" wire:click="$set('showConvertModal', false)"></button>
                </div>
                <div class="modal-body">
                    <p>This will create a new Customer from this lead's data.</p>
                    <div class="form-check form-switch mb-3">
                        <input type="checkbox" class="form-check-input" wire:model="createContact" id="createContactToggle">
                        <label class="form-check-label" for="createContactToggle">Also create a primary contact</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="$set('showConvertModal', false)">Cancel</button>
                    <button type="button" class="btn btn-success btn-wave" wire:click="convert" wire:loading.attr="disabled">
                        <span wire:loading wire:target="convert" class="spinner-border spinner-border-sm me-1"></span>
                        Convert Lead
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Mark Lost Modal --}}
    @if($showLostModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color:rgba(0,0,0,.5)">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Mark Lead as Lost</h6>
                    <button type="button" class="btn-close" wire:click="$set('showLostModal', false)"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Lost Reason <span class="text-danger">*</span></label>
                    <textarea wire:model="lostReason" class="form-control @error('lostReason') is-invalid @enderror" rows="3"></textarea>
                    @error('lostReason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="$set('showLostModal', false)">Close</button>
                    <button type="button" class="btn btn-danger btn-wave" wire:click="markLost" wire:loading.attr="disabled">
                        <span wire:loading wire:target="markLost" class="spinner-border spinner-border-sm me-1"></span>
                        Mark Lost
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
