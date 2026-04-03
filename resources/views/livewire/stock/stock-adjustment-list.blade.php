<div>
    @section('title', 'Stock Adjustments')

    <x-page-header title="Stock Adjustments" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Adjustments'],
    ]">
        <x-slot:actions>
            @can('stock.adjust')
                <a href="{{ route('stock.adjustments.create') }}" class="btn btn-primary btn-wave" wire:navigate>
                    <i class="ri-add-line me-1"></i> New Adjustment
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card custom-card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex gap-2 flex-wrap">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" style="width:220px" placeholder="Search adjustments...">
                <select wire:model.live="statusFilter" class="form-select form-select-sm" style="width:140px">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="approved">Approved</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>Number</th>
                            <th>Warehouse</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Adjusted By</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($adjustments as $adj)
                        <tr wire:key="adj-{{ $adj->id }}">
                            <td><code>{{ $adj->adjustment_number }}</code></td>
                            <td>{{ $adj->warehouse?->name }}</td>
                            <td>{{ Str::limit($adj->reason, 40) }}</td>
                            <td>
                                <span class="badge bg-{{ $adj->status === 'approved' ? 'success' : ($adj->status === 'draft' ? 'warning' : 'danger') }}-transparent">
                                    {{ ucfirst($adj->status) }}
                                </span>
                            </td>
                            <td>{{ $adj->adjustedBy?->name }}</td>
                            <td>{{ $adj->adjusted_at ? format_date($adj->adjusted_at) : format_date($adj->created_at) }}</td>
                            <td class="text-end">
                                <a href="{{ route('stock.adjustments.show', $adj) }}"
                                   class="btn btn-sm btn-outline-primary btn-wave" wire:navigate>
                                    <i class="ri-eye-line"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No adjustments found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($adjustments->hasPages())
        <div class="card-footer">
            {{ $adjustments->links() }}
        </div>
        @endif
    </div>
</div>
