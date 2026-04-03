<div>
    @section('title', 'Adjustment ' . $adjustment->adjustment_number)

    <x-page-header :title="'Adjustment: ' . $adjustment->adjustment_number" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Adjustments', 'route' => 'stock.adjustments.index'],
        ['label' => $adjustment->adjustment_number],
    ]">
        <x-slot:actions>
            @if($adjustment->status === 'draft')
                @can('stock.approve-adjustments')
                    <button class="btn btn-success btn-wave"
                            wire:click="approve"
                            wire:confirm="Approve this adjustment? Stock levels will be updated.">
                        <i class="ri-check-line me-1"></i> Approve
                    </button>
                @endcan
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
                            <span class="badge bg-{{ $adjustment->status === 'approved' ? 'success' : ($adjustment->status === 'draft' ? 'warning' : 'danger') }}-transparent">{{ ucfirst($adjustment->status) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Warehouse</span>
                            <span>{{ $adjustment->warehouse?->name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Reason</span>
                            <span>{{ $adjustment->reason }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Adjusted By</span>
                            <span>{{ $adjustment->adjustedBy?->name }}</span>
                        </li>
                        @if($adjustment->approvedBy)
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Approved By</span>
                            <span>{{ $adjustment->approvedBy->name }}</span>
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
                                    <th>Type</th>
                                    <th class="text-end">Quantity</th>
                                    <th class="text-end">Unit Cost</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($adjustment->items as $item)
                                <tr>
                                    <td>{{ $item->product?->name }}{{ $item->productVariant ? ' - ' . $item->productVariant->name : '' }}</td>
                                    <td><span class="badge bg-{{ $item->type === 'addition' ? 'success' : 'danger' }}-transparent">{{ ucfirst($item->type) }}</span></td>
                                    <td class="text-end">{{ number_format((float) $item->quantity, 2) }}</td>
                                    <td class="text-end">{{ format_currency((float) $item->unit_cost, 4) }}</td>
                                    <td>{{ $item->reason ?? '—' }}</td>
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
