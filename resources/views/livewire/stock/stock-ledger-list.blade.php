<div>
    @section('title', 'Stock Ledger')

    <x-page-header title="Stock Ledger" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Stock Ledger'],
    ]" />

    <div class="card custom-card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex gap-2 flex-wrap">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" style="width:220px" placeholder="Search by product...">
                <select wire:model.live="movementType" class="form-select form-select-sm" style="width:180px">
                    <option value="">All Movements</option>
                    <option value="purchase">Purchase</option>
                    <option value="sale">Sale</option>
                    <option value="adjustment_in">Adjustment In</option>
                    <option value="adjustment_out">Adjustment Out</option>
                    <option value="transfer_in">Transfer In</option>
                    <option value="transfer_out">Transfer Out</option>
                    <option value="return_in">Return In</option>
                    <option value="return_out">Return Out</option>
                    <option value="production_in">Production In</option>
                    <option value="production_out">Production Out</option>
                    <option value="opening_balance">Opening Balance</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Warehouse</th>
                            <th>Movement</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Unit Cost</th>
                            <th class="text-end">Balance</th>
                            <th>By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                        <tr wire:key="sl-{{ $entry->id }}">
                            <td>{{ $entry->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $entry->product?->name }}</td>
                            <td>{{ $entry->warehouse?->name }}</td>
                            <td><span class="badge bg-{{ $entry->quantity >= 0 ? 'success' : 'danger' }}-transparent">{{ str_replace('_', ' ', ucfirst($entry->movement_type)) }}</span></td>
                            <td class="text-end {{ $entry->quantity >= 0 ? 'text-success' : 'text-danger' }}">{{ $entry->quantity >= 0 ? '+' : '' }}{{ number_format((float) $entry->quantity, 2) }}</td>
                            <td class="text-end">{{ number_format((float) $entry->unit_cost, 4) }}</td>
                            <td class="text-end fw-medium">{{ number_format((float) $entry->running_balance, 2) }}</td>
                            <td>{{ $entry->createdBy?->name ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No ledger entries found.</td>
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
