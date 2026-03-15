<div>
    @section('title', 'Trial Balance')

    <x-page-header title="Trial Balance" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Reports'],
        ['label' => 'Trial Balance'],
    ]">
        <x-slot:actions>
            <div class="d-flex gap-2">
                <a href="{{ route('accounting.reports.balance-sheet') }}" class="btn btn-outline-primary btn-sm btn-wave" wire:navigate>Balance Sheet</a>
                <a href="{{ route('accounting.reports.profit-and-loss') }}" class="btn btn-outline-primary btn-sm btn-wave" wire:navigate>Profit & Loss</a>
            </div>
        </x-slot:actions>
    </x-page-header>

    {{-- Filters --}}
    <div class="card custom-card mb-3">
        <div class="card-body py-3">
            <div class="d-flex gap-3 flex-wrap align-items-end">
                <div>
                    <label class="form-label mb-1 fs-12">Fiscal Year</label>
                    <select wire:model.live="fiscalYearId" class="form-select form-select-sm" style="width:200px">
                        <option value="">All Fiscal Years</option>
                        @foreach($fiscalYears as $fy)
                            <option value="{{ $fy->id }}">{{ $fy->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label mb-1 fs-12">As Of Date</label>
                    <input type="date" wire:model.live="asOfDate" class="form-control form-control-sm" style="width:180px">
                </div>
            </div>
        </div>
    </div>

    {{-- Report --}}
    <div class="card custom-card">
        <div class="card-header">
            <div class="card-title">Trial Balance as of {{ $asOfDate ? \Carbon\Carbon::parse($asOfDate)->format('d M Y') : 'All Time' }}</div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Account Name</th>
                            <th>Type</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report['accounts'] as $row)
                        <tr wire:key="tb-{{ $row->id }}">
                            <td><code>{{ $row->code }}</code></td>
                            <td>{{ $row->name }}</td>
                            <td>
                                @php
                                    $typeColors = ['asset' => 'primary', 'liability' => 'warning', 'equity' => 'info', 'revenue' => 'success', 'expense' => 'danger'];
                                @endphp
                                <span class="badge bg-{{ $typeColors[$row->type] ?? 'secondary' }}-transparent">{{ ucfirst($row->type) }}</span>
                            </td>
                            <td class="text-end">{{ $row->debit_balance > 0 ? number_format($row->debit_balance, 2) : '' }}</td>
                            <td class="text-end">{{ $row->credit_balance > 0 ? number_format($row->credit_balance, 2) : '' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No posted journal entries found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($report['accounts']->isNotEmpty())
                    <tfoot>
                        <tr class="fw-bold table-light">
                            <td colspan="3" class="text-end">Totals:</td>
                            <td class="text-end">{{ number_format($report['total_debit'], 2) }}</td>
                            <td class="text-end">{{ number_format($report['total_credit'], 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end fw-medium">Balance Check:</td>
                            <td colspan="2" class="text-center">
                                @if(abs($report['total_debit'] - $report['total_credit']) < 0.01)
                                    <span class="badge bg-success-transparent px-3">Balanced</span>
                                @else
                                    <span class="badge bg-danger-transparent px-3">Unbalanced ({{ number_format(abs($report['total_debit'] - $report['total_credit']), 2) }})</span>
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
