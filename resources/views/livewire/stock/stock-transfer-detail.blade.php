<div>
    @section('title', 'Transfer ' . $transfer->transfer_number)

    <x-page-header :title="'Transfer: ' . $transfer->transfer_number" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Transfers', 'route' => 'stock.transfers.index'],
        ['label' => $transfer->transfer_number],
    ]">
        <x-slot:actions>
            @if($transfer->status === 'draft')
                <button class="btn btn-info btn-wave" wire:click="ship" wire:confirm="Ship this transfer? Stock will be deducted from source warehouse.">
                    <i class="ri-truck-line me-1"></i> Ship
                </button>
                <button class="btn btn-outline-danger btn-wave" wire:click="cancel" wire:confirm="Cancel this transfer?">
                    <i class="ri-close-line me-1"></i> Cancel
                </button>
            @elseif($transfer->status === 'in_transit')
                <button class="btn btn-success btn-wave" wire:click="complete" wire:confirm="Complete this transfer? Stock will be added to destination warehouse.">
                    <i class="ri-check-line me-1"></i> Complete
                </button>
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="row">
        <div class="col-xl-4">
            <div class="card custom-card">
                <div class="card-header"><div class="card-title">Details</div></div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Status</span>
                            @php
                                $color = match($transfer->status) {
                                    'completed' => 'success',
                                    'in_transit' => 'info',
                                    'draft' => 'warning',
                                    default => 'danger',
                                };
                            @endphp
                            <span class="badge bg-{{ $color }}-transparent">{{ str_replace('_', ' ', ucfirst($transfer->status)) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">From</span>
                            <span>{{ $transfer->fromWarehouse?->name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">To</span>
                            <span>{{ $transfer->toWarehouse?->name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Initiated By</span>
                            <span>{{ $transfer->initiatedBy?->name }}</span>
                        </li>
                        @if($transfer->completedBy)
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Completed By</span>
                            <span>{{ $transfer->completedBy->name }}</span>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card custom-card">
                <div class="card-header"><div class="card-title">Items</div></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Quantity</th>
                                    <th class="text-end">Unit Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transfer->items as $item)
                                <tr>
                                    <td>{{ $item->product?->name }}{{ $item->productVariant ? ' - ' . $item->productVariant->name : '' }}</td>
                                    <td class="text-end">{{ number_format((float) $item->quantity, 2) }}</td>
                                    <td class="text-end">{{ format_currency((float) $item->unit_cost, 4) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
