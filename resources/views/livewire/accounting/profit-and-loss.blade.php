<div>
    @section('title', 'Profit & Loss')

    <x-page-header title="Profit & Loss Statement" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Reports'],
        ['label' => 'Profit & Loss'],
    ]">
        <x-slot:actions>
            <div class="d-flex gap-2">
                <a href="{{ route('accounting.reports.trial-balance') }}" class="btn btn-outline-primary btn-sm btn-wave" wire:navigate>Trial Balance</a>
                <a href="{{ route('accounting.reports.balance-sheet') }}" class="btn btn-outline-primary btn-sm btn-wave" wire:navigate>Balance Sheet</a>
            </div>
        </x-slot:actions>
    </x-page-header>

    <div class="card custom-card mb-3">
        <div class="card-body py-3">
            <div class="d-flex gap-3 flex-wrap align-items-end">
                <div>
                    <label class="form-label mb-1 fs-12">From Date</label>
                    <input type="date" wire:model.live="dateFrom" class="form-control form-control-sm" style="width:180px">
                </div>
                <div>
                    <label class="form-label mb-1 fs-12">To Date</label>
                    <input type="date" wire:model.live="dateTo" class="form-control form-control-sm" style="width:180px">
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Revenue --}}
        <div class="col-md-6">
            <div class="card custom-card">
                <div class="card-header bg-success-transparent">
                    <div class="card-title">Revenue</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr><th>Account</th><th class="text-end">Amount</th></tr>
                            </thead>
                            <tbody>
                                @forelse($report['revenue'] as $item)
                                <tr>
                                    <td>
                                        <code>{{ $item->code }}</code>
                                        <span class="ms-1">{{ $item->name }}</span>
                                    </td>
                                    <td class="text-end">{{ number_format($item->balance, 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="2" class="text-center text-muted py-3">No revenue recorded</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold table-light">
                                    <td>Total Revenue</td>
                                    <td class="text-end">{{ number_format($report['total_revenue'], 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Expenses --}}
        <div class="col-md-6">
            <div class="card custom-card">
                <div class="card-header bg-danger-transparent">
                    <div class="card-title">Expenses</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr><th>Account</th><th class="text-end">Amount</th></tr>
                            </thead>
                            <tbody>
                                @forelse($report['expenses'] as $item)
                                <tr>
                                    <td>
                                        <code>{{ $item->code }}</code>
                                        <span class="ms-1">{{ $item->name }}</span>
                                    </td>
                                    <td class="text-end">{{ number_format($item->balance, 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="2" class="text-center text-muted py-3">No expenses recorded</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold table-light">
                                    <td>Total Expenses</td>
                                    <td class="text-end">{{ number_format($report['total_expenses'], 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Net Profit Summary --}}
    <div class="card custom-card mt-3">
        <div class="card-body">
            <div class="d-flex justify-content-center align-items-center gap-4 flex-wrap">
                <div class="text-center">
                    <div class="text-muted fs-12">Total Revenue</div>
                    <div class="fw-bold fs-16 text-success">{{ number_format($report['total_revenue'], 2) }}</div>
                </div>
                <div class="fs-20 fw-bold text-muted">−</div>
                <div class="text-center">
                    <div class="text-muted fs-12">Total Expenses</div>
                    <div class="fw-bold fs-16 text-danger">{{ number_format($report['total_expenses'], 2) }}</div>
                </div>
                <div class="fs-20 fw-bold text-muted">=</div>
                <div class="text-center">
                    <div class="text-muted fs-12">Net {{ $report['net_profit'] >= 0 ? 'Profit' : 'Loss' }}</div>
                    <div class="fw-bold fs-20 {{ $report['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format(abs($report['net_profit']), 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
