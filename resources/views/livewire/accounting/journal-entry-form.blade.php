<div>
    @section('title', 'New Journal Entry')

    <x-page-header title="New Journal Entry" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Journal Entries', 'route' => 'accounting.journal-entries.index'],
        ['label' => 'New Entry'],
    ]" />

    {{-- Header --}}
    <div class="card custom-card">
        <div class="card-header"><div class="card-title">Entry Details</div></div>
        <div class="card-body">
            <div class="row gy-3">
                <div class="col-md-3">
                    <label class="form-label">Fiscal Year <span class="text-danger">*</span>
                        <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="The accounting period this entry belongs to"></i>
                    </label>
                    <select wire:model="fiscal_year_id" class="form-select @error('fiscal_year_id') is-invalid @enderror">
                        <option value="">— Select —</option>
                        @foreach($fiscalYears as $fy)
                            <option value="{{ $fy->id }}">{{ $fy->name }}</option>
                        @endforeach
                    </select>
                    @error('fiscal_year_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date <span class="text-danger">*</span>
                        <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="The effective date of this transaction"></i>
                    </label>
                    <input type="date" wire:model="date" class="form-control @error('date') is-invalid @enderror">
                    @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Reference
                        <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Optional external reference number (e.g. invoice #, receipt #)"></i>
                    </label>
                    <input type="text" wire:model="reference" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Description <span class="text-danger">*</span>
                        <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Brief explanation of the purpose of this entry"></i>
                    </label>
                    <input type="text" wire:model="description" class="form-control @error('description') is-invalid @enderror">
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Notes
                        <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Internal notes visible only to accounting staff"></i>
                    </label>
                    <textarea wire:model="notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Line Items --}}
    <div class="card custom-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="card-title">Line Items</div>
            <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addLine">
                <i class="ri-add-line me-1"></i> Add Line
            </button>
        </div>
        <div class="card-body p-0">
            @error('lines') <div class="alert alert-danger m-3">{{ $message }}</div> @enderror
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:35%">Account <span class="text-danger">*</span></th>
                            <th style="width:25%">Description</th>
                            <th style="width:15%" class="text-end">Debit</th>
                            <th style="width:15%" class="text-end">Credit</th>
                            <th style="width:10%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lines as $i => $line)
                        <tr wire:key="line-{{ $i }}">
                            <td>
                                <select wire:model="lines.{{ $i }}.account_id"
                                        class="form-select form-select-sm @error('lines.'.$i.'.account_id') is-invalid @enderror">
                                    <option value="">— Select Account —</option>
                                    @foreach($accounts as $acct)
                                        <option value="{{ $acct->id }}">{{ $acct->code }} — {{ $acct->name }}</option>
                                    @endforeach
                                </select>
                                @error('lines.'.$i.'.account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </td>
                            <td>
                                <input type="text" wire:model="lines.{{ $i }}.description"
                                       class="form-control form-control-sm" placeholder="Line memo...">
                            </td>
                            <td>
                                <input type="number" step="0.0001" wire:model.live="lines.{{ $i }}.debit"
                                       class="form-control form-control-sm text-end" placeholder="0.00">
                            </td>
                            <td>
                                <input type="number" step="0.0001" wire:model.live="lines.{{ $i }}.credit"
                                       class="form-control form-control-sm text-end" placeholder="0.00">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeLine({{ $i }})">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="2" class="text-end">Totals:</td>
                            <td class="text-end">{{ format_currency($this->totalDebit) }}</td>
                            <td class="text-end">{{ format_currency($this->totalCredit) }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-end fw-medium">Difference:</td>
                            <td colspan="2" class="text-center">
                                @if($this->isBalanced && $this->totalDebit > 0)
                                    <span class="badge bg-success-transparent">Balanced</span>
                                @elseif($this->totalDebit == 0 && $this->totalCredit == 0)
                                    <span class="badge bg-secondary-transparent">Enter amounts</span>
                                @else
                                    <span class="badge bg-danger-transparent">Off by {{ format_currency($this->difference) }}</span>
                                @endif
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="d-flex justify-content-between mb-4">
        <a href="{{ route('accounting.journal-entries.index') }}" class="btn btn-light" wire:navigate>Cancel</a>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary btn-wave" wire:click="saveDraft" wire:loading.attr="disabled">
                <span wire:loading wire:target="saveDraft" class="spinner-border spinner-border-sm me-1"></span>
                Save Draft
            </button>
            <button type="button" class="btn btn-success btn-wave" wire:click="saveAndPost" wire:loading.attr="disabled"
                    @if(!$this->isBalanced || $this->totalDebit == 0) disabled @endif>
                <span wire:loading wire:target="saveAndPost" class="spinner-border spinner-border-sm me-1"></span>
                <i class="ri-check-double-line me-1"></i> Save & Post
            </button>
        </div>
    </div>
</div>
