<div>
    @section('title', 'Balance Sheet')

    <x-page-header title="Balance Sheet" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Reports'],
        ['label' => 'Balance Sheet'],
    ]">
        <x-slot:actions>
            <div class="d-flex gap-2">
                <a href="{{ route('accounting.reports.trial-balance') }}" class="btn btn-outline-primary btn-sm btn-wave" wire:navigate>Trial Balance</a>
                <a href="{{ route('accounting.reports.profit-and-loss') }}" class="btn btn-outline-primary btn-sm btn-wave" wire:navigate>Profit & Loss</a>
            </div>
        </x-slot:actions>
    </x-page-header>

    <div class="card custom-card mb-3">
        <div class="card-body py-3">
            <div class="d-flex gap-3 flex-wrap align-items-end">
                <div>
                    <label class="form-label mb-1 fs-12">As Of Date</label>
                    <input type="date" wire:model.live="asOfDate" class="form-control form-control-sm" style="width:180px">
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Assets --}}
        <div class="col-md-6">
            <div class="card custom-card">
                <div class="card-header bg-primary-transparent">
                    <div class="card-title">Assets</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr><th>Account</th><th class="text-end">Balance</th></tr>
                            </thead>
                            <tbody>
                                @forelse($report['assets'] as $item)
                                <tr>
                                    <td>
                                        <code>{{ $item->code }}</code>
                                        <span class="ms-1">{{ $item->name }}</span>
                                    </td>
                                    <td class="text-end">{{ format_currency($item->balance) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="2" class="text-center text-muted py-3">No asset accounts</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold table-light">
                                    <td>Total Assets</td>
                                    <td class="text-end">{{ format_currency($report['total_assets']) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Liabilities & Equity --}}
        <div class="col-md-6">
            <div class="card custom-card mb-3">
                <div class="card-header bg-warning-transparent">
                    <div class="card-title">Liabilities</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr><th>Account</th><th class="text-end">Balance</th></tr>
                            </thead>
                            <tbody>
                                @forelse($report['liabilities'] as $item)
                                <tr>
                                    <td>
                                        <code>{{ $item->code }}</code>
                                        <span class="ms-1">{{ $item->name }}</span>
                                    </td>
                                    <td class="text-end">{{ format_currency($item->balance) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="2" class="text-center text-muted py-3">No liability accounts</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold table-light">
                                    <td>Total Liabilities</td>
                                    <td class="text-end">{{ format_currency($report['total_liabilities']) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card custom-card">
                <div class="card-header bg-info-transparent">
                    <div class="card-title">Equity</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr><th>Account</th><th class="text-end">Balance</th></tr>
                            </thead>
                            <tbody>
                                @forelse($report['equity'] as $item)
                                <tr>
                                    <td>
                                        <code>{{ $item->code }}</code>
                                        <span class="ms-1">{{ $item->name }}</span>
                                    </td>
                                    <td class="text-end">{{ format_currency($item->balance) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="2" class="text-center text-muted py-3">No equity accounts</td></tr>
                                @endforelse
                                @if(abs($report['retained_earnings']) > 0.01)
                                <tr class="table-light">
                                    <td><em>Retained Earnings (calculated)</em></td>
                                    <td class="text-end">{{ format_currency($report['retained_earnings']) }}</td>
                                </tr>
                                @endif
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold table-light">
                                    <td>Total Equity</td>
                                    <td class="text-end">{{ format_currency($report['total_equity']) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Accounting Equation Check --}}
    <div class="card custom-card mt-3">
        <div class="card-body">
            <div class="d-flex justify-content-center align-items-center gap-3 flex-wrap">
                <div class="text-center">
                    <div class="text-muted fs-12">Total Assets</div>
                    <div class="fw-bold fs-16">{{ format_currency($report['total_assets']) }}</div>
                </div>
                <div class="fs-20 fw-bold text-muted">=</div>
                <div class="text-center">
                    <div class="text-muted fs-12">Total Liabilities</div>
                    <div class="fw-bold fs-16">{{ format_currency($report['total_liabilities']) }}</div>
                </div>
                <div class="fs-20 fw-bold text-muted">+</div>
                <div class="text-center">
                    <div class="text-muted fs-12">Total Equity</div>
                    <div class="fw-bold fs-16">{{ format_currency($report['total_equity']) }}</div>
                </div>
                <div class="ms-3">
                    @php $diff = abs($report['total_assets'] - ($report['total_liabilities'] + $report['total_equity'])); @endphp
                    @if($diff < 0.01)
                        <span class="badge bg-success-transparent px-3 py-2">Equation balanced</span>
                    @else
                        <span class="badge bg-danger-transparent px-3 py-2">Off by {{ format_currency($diff) }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
