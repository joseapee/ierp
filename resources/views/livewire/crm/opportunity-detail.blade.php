<div>
    @section('title', 'Opportunity: ' . $opportunity->name)

    <x-page-header :title="'Opportunity: ' . $opportunity->name" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Opportunities', 'route' => 'crm.opportunities.index'],
        ['label' => $opportunity->name],
    ]">
        <x-slot:actions>
            @if(!$opportunity->closed_at)
                @can('opportunities.manage')
                <button class="btn btn-success btn-wave" wire:click="openMarkWonModal">
                    <i class="ri-trophy-line me-1"></i> Mark Won
                </button>
                <button class="btn btn-danger btn-wave" wire:click="openMarkLostModal">
                    <i class="ri-close-circle-line me-1"></i> Mark Lost
                </button>
                @endcan
            @endif
            @can('opportunities.edit')
            <a href="{{ route('crm.opportunities.edit', $opportunity) }}" class="btn btn-outline-primary btn-wave" wire:navigate>
                <i class="ri-pencil-line me-1"></i> Edit
            </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    {{-- Opportunity Info --}}
    <div class="row">
        <div class="col-lg-8">
            <div class="card custom-card">
                <div class="card-header"><div class="card-title">Opportunity Information</div></div>
                <div class="card-body">
                    <div class="row gy-2">
                        <div class="col-md-4"><span class="text-muted">Name:</span><br><strong>{{ $opportunity->name }}</strong></div>
                        <div class="col-md-4"><span class="text-muted">Customer:</span><br><strong>{{ $opportunity->customer?->name ?? '—' }}</strong></div>
                        <div class="col-md-4">
                            <span class="text-muted">Stage:</span><br>
                            @if($opportunity->pipelineStage)
                            <span class="badge" style="background-color: {{ $opportunity->pipelineStage->color }}20; color: {{ $opportunity->pipelineStage->color }};">
                                {{ $opportunity->pipelineStage->name }}
                            </span>
                            @endif
                        </div>
                        <div class="col-md-4"><span class="text-muted">Contact:</span><br>{{ $opportunity->contact?->full_name ?? '—' }}</div>
                        <div class="col-md-4"><span class="text-muted">Assigned To:</span><br>{{ $opportunity->assignedUser?->name ?? '—' }}</div>
                        <div class="col-md-4"><span class="text-muted">Expected Close:</span><br>{{ $opportunity->expected_close_date ? format_date($opportunity->expected_close_date) : '—' }}</div>
                        @if($opportunity->closed_at)
                        <div class="col-md-4"><span class="text-muted">Closed At:</span><br>{{ format_datetime($opportunity->closed_at) }}</div>
                        @endif
                        @if($opportunity->lost_reason)
                        <div class="col-12"><span class="text-muted">Lost Reason:</span><br>{{ $opportunity->lost_reason }}</div>
                        @endif
                        @if($opportunity->notes)
                        <div class="col-12"><span class="text-muted">Notes:</span><br>{{ $opportunity->notes }}</div>
                        @endif
                    </div>
                </div>
            </div>

            @if($opportunity->salesOrder)
            <div class="card custom-card">
                <div class="card-header"><div class="card-title">Linked Sales Order</div></div>
                <div class="card-body">
                    <a href="{{ route('sales.orders.show', $opportunity->salesOrder) }}" wire:navigate class="text-primary fw-medium">
                        {{ $opportunity->salesOrder->order_number }}
                    </a>
                </div>
            </div>
            @endif
        </div>
        <div class="col-lg-4">
            <div class="card custom-card">
                <div class="card-header"><div class="card-title">Value & Probability</div></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Expected Value:</span>
                        <span class="fw-bold">{{ format_currency((float)$opportunity->expected_value) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Probability:</span>
                        <span class="fw-bold">{{ number_format((float)$opportunity->probability, 0) }}%</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Weighted Value:</span>
                        <span>{{ format_currency((float)$opportunity->weighted_value) }}</span>
                    </div>
                </div>
            </div>

            {{-- Stage Move --}}
            @if(!$opportunity->closed_at)
            <div class="card custom-card">
                <div class="card-header"><div class="card-title">Move Stage</div></div>
                <div class="card-body">
                    @can('opportunities.manage')
                    <div class="d-flex gap-2">
                        <select wire:model="selectedStageId" class="form-select form-select-sm">
                            @foreach($stages as $stage)
                                <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm btn-primary btn-wave" wire:click="moveToStage" wire:loading.attr="disabled">
                            Move
                        </button>
                    </div>
                    @endcan
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Mark Won Modal --}}
    @if($showMarkWonModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color:rgba(0,0,0,.5)">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Mark Opportunity as Won</h6>
                    <button type="button" class="btn-close" wire:click="$set('showMarkWonModal', false)"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to mark this opportunity as won?</p>
                    <p class="text-muted small">The opportunity will be moved to the "Won" pipeline stage with 100% probability.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="$set('showMarkWonModal', false)">Cancel</button>
                    <button type="button" class="btn btn-success btn-wave" wire:click="markWon" wire:loading.attr="disabled">
                        <span wire:loading wire:target="markWon" class="spinner-border spinner-border-sm me-1"></span>
                        Confirm Won
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Mark Lost Modal --}}
    @if($showMarkLostModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color:rgba(0,0,0,.5)">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Mark Opportunity as Lost</h6>
                    <button type="button" class="btn-close" wire:click="$set('showMarkLostModal', false)"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Reason for lost <span class="text-danger">*</span></label>
                    <textarea wire:model="lost_reason" class="form-control @error('lost_reason') is-invalid @enderror" rows="3"></textarea>
                    @error('lost_reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="$set('showMarkLostModal', false)">Close</button>
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
