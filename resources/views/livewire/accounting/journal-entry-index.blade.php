<div>
    @section('title', 'Journal Entries')

    <x-page-header title="Journal Entries" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Journal Entries'],
    ]">
        <x-slot:actions>
            <a href="{{ route('accounting.journal-entries.create') }}" class="btn btn-primary btn-wave" wire:navigate>
                <i class="ri-add-line me-1"></i> New Entry
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="card custom-card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex gap-2 flex-wrap">
                <input type="text" wire:model.live.debounce.300ms="search"
                       class="form-control form-control-sm" style="width:220px"
                       placeholder="Search entry #, description...">
                <select wire:model.live="statusFilter" class="form-select form-select-sm" style="width:140px">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="posted">Posted</option>
                    <option value="voided">Voided</option>
                </select>
                <select wire:model.live="fiscalYearFilter" class="form-select form-select-sm" style="width:180px">
                    <option value="">All Fiscal Years</option>
                    @foreach($fiscalYears as $fy)
                        <option value="{{ $fy->id }}">{{ $fy->name }}</option>
                    @endforeach
                </select>
                <input type="date" wire:model.live="dateFrom" class="form-control form-control-sm" style="width:150px" placeholder="From">
                <input type="date" wire:model.live="dateTo" class="form-control form-control-sm" style="width:150px" placeholder="To">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>Entry #</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Reference</th>
                            <th class="text-end">Total Debit</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                        <tr wire:key="je-{{ $entry->id }}">
                            <td><code>{{ $entry->entry_number }}</code></td>
                            <td>{{ format_date($entry->date) }}</td>
                            <td>
                                <span class="d-inline-block text-truncate" style="max-width:300px;">{{ $entry->description }}</span>
                                @if($entry->source_type)
                                    <span class="badge bg-secondary-transparent fs-9 ms-1">Auto</span>
                                @endif
                            </td>
                            <td>{{ $entry->reference ?? '—' }}</td>
                            <td class="text-end">{{ format_currency((float) $entry->lines->sum('debit')) }}</td>
                            <td>
                                @php
                                    $statusColors = ['draft' => 'warning', 'posted' => 'success', 'voided' => 'danger'];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$entry->status] ?? 'secondary' }}-transparent">{{ ucfirst($entry->status) }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('accounting.journal-entries.show', $entry) }}"
                                   class="btn btn-sm btn-outline-primary btn-wave" wire:navigate>
                                    <i class="ri-eye-line"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No journal entries found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($entries->hasPages())
        <div class="card-footer">
            {{ $entries->links() }}
        </div>
        @endif
    </div>
</div>
