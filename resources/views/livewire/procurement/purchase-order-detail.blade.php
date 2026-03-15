<div>
    @section('title', 'Purchase Order ' . $purchaseOrder->order_number)

    <x-page-header :title="'PO: ' . $purchaseOrder->order_number" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Purchase Orders', 'route' => 'procurement.purchase-orders.index'],
        ['label' => $purchaseOrder->order_number],
    ]">
        <x-slot:actions>
            @if($purchaseOrder->status === 'draft')
                @can('purchase-orders.manage')
                <button class="btn btn-success btn-wave" wire:click="confirm" wire:loading.attr="disabled">
                    <i class="ri-check-line me-1"></i> Confirm
                </button>
                <button class="btn btn-danger btn-wave" wire:click="openCancelModal">
                    <i class="ri-close-line me-1"></i> Cancel
                </button>
                @endcan
            @endif
            @if(in_array($purchaseOrder->status, ['confirmed', 'partially_received']))
                @can('purchase-orders.manage')
                <button class="btn btn-primary btn-wave" wire:click="openReceiveModal">
                    <i class="ri-truck-line me-1"></i> Receive Items
                </button>
                @endcan
            @endif
            @if($purchaseOrder->status === 'confirmed')
                @can('purchase-orders.manage')
                <button class="btn btn-danger btn-wave" wire:click="openCancelModal">
                    <i class="ri-close-line me-1"></i> Cancel
                </button>
                @endcan
            @endif
        </x-slot:actions>
    </x-page-header>

    {{-- Order Info --}}
    <div class="row">
        <div class="col-lg-8">
            <div class="card custom-card">
                <div class="card-header"><div class="card-title">Order Information</div></div>
                <div class="card-body">
                    <div class="row gy-2">
                        <div class="col-md-4"><span class="text-muted">Order Number:</span><br><strong>{{ $purchaseOrder->order_number }}</strong></div>
                        <div class="col-md-4"><span class="text-muted">Supplier:</span><br><strong>{{ $purchaseOrder->supplier?->name }}</strong></div>
                        <div class="col-md-4">
                            <span class="text-muted">Status:</span><br>
                            @php
                                $colors = ['draft' => 'warning', 'confirmed' => 'info', 'partially_received' => 'primary', 'received' => 'success', 'cancelled' => 'danger'];
                            @endphp
                            <span class="badge bg-{{ $colors[$purchaseOrder->status] ?? 'secondary' }}-transparent">
                                {{ str_replace('_', ' ', ucfirst($purchaseOrder->status)) }}
                            </span>
                        </div>
                        <div class="col-md-4"><span class="text-muted">Order Date:</span><br>{{ $purchaseOrder->order_date?->format('Y-m-d') }}</div>
                        <div class="col-md-4"><span class="text-muted">Expected Date:</span><br>{{ $purchaseOrder->expected_date?->format('Y-m-d') ?? '—' }}</div>
                        @if($purchaseOrder->notes)
                        <div class="col-12"><span class="text-muted">Notes:</span><br>{{ $purchaseOrder->notes }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card custom-card">
                <div class="card-header"><div class="card-title">Totals</div></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal:</span>
                        <span>{{ number_format((float)$purchaseOrder->subtotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Tax:</span>
                        <span>{{ number_format((float)$purchaseOrder->tax_amount, 2) }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Total:</span>
                        <span>{{ number_format((float)$purchaseOrder->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Line Items --}}
    <div class="card custom-card">
        <div class="card-header"><div class="card-title">Line Items</div></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Warehouse</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Tax</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Received</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseOrder->items as $item)
                        <tr>
                            <td>{{ $item->product?->name }}</td>
                            <td>{{ $item->warehouse?->name }}</td>
                            <td class="text-end">{{ number_format((float)$item->quantity, 4) }}</td>
                            <td class="text-end">{{ number_format((float)$item->unit_price, 2) }}</td>
                            <td class="text-end">{{ number_format((float)$item->tax_amount, 2) }}</td>
                            <td class="text-end">{{ number_format((float)$item->total, 2) }}</td>
                            <td class="text-end">
                                <span class="{{ (float)$item->quantity_received >= (float)$item->quantity ? 'text-success' : '' }}">
                                    {{ number_format((float)$item->quantity_received, 4) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Receive Modal --}}
    @if($showReceiveModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color:rgba(0,0,0,.5)">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Receive Items</h6>
                    <button type="button" class="btn-close" wire:click="$set('showReceiveModal', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Remaining</th>
                                    <th style="width:200px">Qty to Receive</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($receiveItems as $i => $ri)
                                @php $poItem = $purchaseOrder->items->firstWhere('id', $ri['purchase_order_item_id']); @endphp
                                <tr>
                                    <td>{{ $poItem?->product?->name }}</td>
                                    <td class="text-end">{{ number_format($ri['max'], 4) }}</td>
                                    <td>
                                        <input type="number" step="0.0001" max="{{ $ri['max'] }}"
                                               wire:model="receiveItems.{{ $i }}.quantity_received"
                                               class="form-control form-control-sm">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="$set('showReceiveModal', false)">Cancel</button>
                    <button type="button" class="btn btn-primary btn-wave" wire:click="receiveItems" wire:loading.attr="disabled">
                        <span wire:loading wire:target="receiveItems" class="spinner-border spinner-border-sm me-1"></span>
                        Confirm Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Cancel Modal --}}
    @if($showCancelModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color:rgba(0,0,0,.5)">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Cancel Purchase Order</h6>
                    <button type="button" class="btn-close" wire:click="$set('showCancelModal', false)"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Reason for cancellation <span class="text-danger">*</span></label>
                    <textarea wire:model="cancellationReason" class="form-control @error('cancellationReason') is-invalid @enderror" rows="3"></textarea>
                    @error('cancellationReason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="$set('showCancelModal', false)">Close</button>
                    <button type="button" class="btn btn-danger btn-wave" wire:click="cancelOrder" wire:loading.attr="disabled">
                        <span wire:loading wire:target="cancelOrder" class="spinner-border spinner-border-sm me-1"></span>
                        Cancel Order
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
