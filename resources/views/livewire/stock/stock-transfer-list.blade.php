<div>
    @section('title', 'Stock Transfers')

    <x-page-header title="Stock Transfers" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Transfers'],
    ]">
        <x-slot:actions>
            @can('stock.transfer')
                <a href="{{ route('stock.transfers.create') }}" class="btn btn-primary btn-wave" wire:navigate>
                    <i class="ri-add-line me-1"></i> New Transfer
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card custom-card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex gap-2 flex-wrap">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" style="width:220px" placeholder="Search transfers...">
                <select wire:model.live="statusFilter" class="form-select form-select-sm" style="width:140px">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="in_transit">In Transit</option>
                    <option value="completed">Completed</option>
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
                            <th>From</th>
                            <th>To</th>
                            <th>Status</th>
                            <th>Initiated By</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transfers as $transfer)
                        <tr wire:key="tr-{{ $transfer->id }}">
                            <td><code>{{ $transfer->transfer_number }}</code></td>
                            <td>{{ $transfer->fromWarehouse?->name }}</td>
                            <td>{{ $transfer->toWarehouse?->name }}</td>
                            <td>
                                @php
                                    $color = match($transfer->status) {
                                        'completed' => 'success',
                                        'in_transit' => 'info',
                                        'draft' => 'warning',
                                        default => 'danger',
                                    };
                                @endphp
                                <span class="badge bg-{{ $color }}-transparent">{{ str_replace('_', ' ', ucfirst($transfer->status)) }}</span>
                            </td>
                            <td>{{ $transfer->initiatedBy?->name }}</td>
                            <td>{{ $transfer->created_at->format('Y-m-d') }}</td>
                            <td class="text-end">
                                <a href="{{ route('stock.transfers.show', $transfer) }}"
                                   class="btn btn-sm btn-outline-primary btn-wave" wire:navigate>
                                    <i class="ri-eye-line"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No transfers found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($transfers->hasPages())
        <div class="card-footer">
            {{ $transfers->links() }}
        </div>
        @endif
    </div>
</div>
