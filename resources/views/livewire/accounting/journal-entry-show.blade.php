<div>
    @section('title', 'Journal Entry ' . $entry->entry_number)

    <x-page-header :title="'Journal Entry: ' . $entry->entry_number" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Journal Entries', 'route' => 'accounting.journal-entries.index'],
        ['label' => $entry->entry_number],
    ]">
        <x-slot:actions>
            @if($entry->status === 'draft')
                <button type="button" class="btn btn-success btn-wave" wire:click="post"
                        wire:confirm="Are you sure you want to post this journal entry? This action cannot be undone."
                        wire:loading.attr="disabled">
                    <i class="ri-check-double-line me-1"></i> Post Entry
                </button>
            @elseif($entry->status === 'posted')
                <button type="button" class="btn btn-danger btn-wave" wire:click="openVoidModal">
                    <i class="ri-close-circle-line me-1"></i> Void Entry
                </button>
            @endif
        </x-slot:actions>
    </x-page-header>

    {{-- Entry Header --}}
    <div class="row g-3 mb-3">
        <div class="col-md-8">
            <div class="card custom-card">
                <div class="card-header"><div class="card-title">Entry Details</div></div>
                <div class="card-body">
                    <div class="row gy-2">
                        <div class="col-sm-3"><span class="text-muted">Entry #</span></div>
                        <div class="col-sm-9"><code>{{ $entry->entry_number }}</code></div>
                        <div class="col-sm-3"><span class="text-muted">Date</span></div>
                        <div class="col-sm-9">{{ $entry->date->format('d M Y') }}</div>
                        <div class="col-sm-3"><span class="text-muted">Description</span></div>
                        <div class="col-sm-9">{{ $entry->description }}</div>
                        <div class="col-sm-3"><span class="text-muted">Reference</span></div>
                        <div class="col-sm-9">{{ $entry->reference ?? '—' }}</div>
                        <div class="col-sm-3"><span class="text-muted">Fiscal Year</span></div>
                        <div class="col-sm-9">{{ $entry->fiscalYear->name }}</div>
                        @if($entry->notes)
                        <div class="col-sm-3"><span class="text-muted">Notes</span></div>
                        <div class="col-sm-9">{{ $entry->notes }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card custom-card">
                <div class="card-header"><div class="card-title">Status</div></div>
                <div class="card-body">
                    @php
                        $statusColors = ['draft' => 'warning', 'posted' => 'success', 'voided' => 'danger'];
                    @endphp
                    <div class="mb-3">
                        <span class="badge bg-{{ $statusColors[$entry->status] ?? 'secondary' }}-transparent fs-14 px-3 py-2">
                            {{ ucfirst($entry->status) }}
                        </span>
                    </div>
                    @if($entry->posted_at)
                    <div class="mb-2">
                        <small class="text-muted d-block">Posted</small>
                        <span>{{ $entry->posted_at->format('d M Y H:i') }}</span>
                        @if($entry->postedByUser)
                            <small class="text-muted">by {{ $entry->postedByUser->name }}</small>
                        @endif
                    </div>
                    @endif
                    @if($entry->voided_at)
                    <div class="mb-2">
                        <small class="text-muted d-block">Voided</small>
                        <span>{{ $entry->voided_at->format('d M Y H:i') }}</span>
                        @if($entry->voidedByUser)
                            <small class="text-muted">by {{ $entry->voidedByUser->name }}</small>
                        @endif
                    </div>
                    @endif
                    @if($entry->source_type)
                    <div>
                        <small class="text-muted d-block">Source</small>
                        <span class="badge bg-info-transparent">{{ class_basename($entry->source_type) }} #{{ $entry->source_id }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Line Items --}}
    <div class="card custom-card">
        <div class="card-header"><div class="card-title">Line Items</div></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Account</th>
                            <th>Description</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entry->lines as $line)
                        <tr>
                            <td>
                                <code>{{ $line->account->code }}</code>
                                <span class="ms-1">{{ $line->account->name }}</span>
                            </td>
                            <td>{{ $line->description ?? '—' }}</td>
                            <td class="text-end">{{ (float) $line->debit > 0 ? number_format((float) $line->debit, 2) : '' }}</td>
                            <td class="text-end">{{ (float) $line->credit > 0 ? number_format((float) $line->credit, 2) : '' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="2" class="text-end">Totals:</td>
                            <td class="text-end">{{ number_format((float) $entry->lines->sum('debit'), 2) }}</td>
                            <td class="text-end">{{ number_format((float) $entry->lines->sum('credit'), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <a href="{{ route('accounting.journal-entries.index') }}" class="btn btn-light" wire:navigate>
            <i class="ri-arrow-left-line me-1"></i> Back to Journal Entries
        </a>
    </div>

    {{-- Void Modal --}}
    @if($showVoidModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color:rgba(0,0,0,0.5)">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Void Journal Entry</h6>
                    <button type="button" class="btn-close" wire:click="$set('showVoidModal', false)"></button>
                </div>
                <form wire:submit="confirmVoid">
                    <div class="modal-body">
                        <p class="text-muted">This will create a reversing entry and mark <code>{{ $entry->entry_number }}</code> as voided.</p>
                        <label class="form-label">Reason for Voiding <span class="text-danger">*</span></label>
                        <textarea wire:model="voidReason" class="form-control @error('voidReason') is-invalid @enderror"
                                  rows="3" placeholder="Enter the reason for voiding this entry..."></textarea>
                        @error('voidReason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="$set('showVoidModal', false)">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-wave">
                            <span wire:loading.remove wire:target="confirmVoid">Void Entry</span>
                            <span wire:loading wire:target="confirmVoid">Processing...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
