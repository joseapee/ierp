<div>
    @section('title', 'Sales Order ' . $order->order_number)

    <x-page-header :title="'SO: ' . $order->order_number" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Sales Orders', 'route' => 'sales.orders.index'],
        ['label' => $order->order_number],
    ]">
        <x-slot:actions>
            @if($order->status === 'draft')
                @can('sales-orders.manage')
                <button class="btn btn-success btn-wave" wire:click="confirm" wire:loading.attr="disabled">
                    <i class="ri-check-line me-1"></i> Confirm
                </button>
                <button class="btn btn-danger btn-wave" wire:click="openCancelModal">
                    <i class="ri-close-line me-1"></i> Cancel
                </button>
                @endcan
            @endif
            @if(in_array($order->status, ['confirmed', 'partially_fulfilled']))
                @can('sales-orders.manage')
                <button class="btn btn-primary btn-wave" wire:click="openFulfillModal">
                    <i class="ri-shopping-bag-line me-1"></i> Fulfill Items
                </button>
                @endcan
            @endif
            @if($order->status === 'confirmed')
                @can('sales-orders.manage')
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
                        <div class="col-md-4"><span class="text-muted">Order Number:</span><br><strong>{{ $order->order_number }}</strong></div>
                        <div class="col-md-4"><span class="text-muted">Customer:</span><br><strong>{{ $order->customer?->name }}</strong></div>
                        <div class="col-md-4">
                            <span class="text-muted">Status:</span><br>
                            @php
                                $colors = ['draft' => 'warning', 'confirmed' => 'info', 'partially_fulfilled' => 'primary', 'fulfilled' => 'success', 'cancelled' => 'danger'];
                            @endphp
                            <span class="badge bg-{{ $colors[$order->status] ?? 'secondary' }}-transparent">
                                {{ str_replace('_', ' ', ucfirst($order->status)) }}
                            </span>
                        </div>
                        <div class="col-md-4"><span class="text-muted">Order Date:</span><br>{{ $order->order_date?->format('Y-m-d') }}</div>
                        <div class="col-md-4"><span class="text-muted">Due Date:</span><br>{{ $order->due_date?->format('Y-m-d') ?? '—' }}</div>
                        @if($order->notes)
                        <div class="col-12"><span class="text-muted">Notes:</span><br>{{ $order->notes }}</div>
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
                        <span>{{ number_format((float)$order->subtotal, 2) }}</span>
                    </div>
                    @if((float)$order->discount_amount > 0)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Discount:</span>
                        <span class="text-danger">-{{ number_format((float)$order->discount_amount, 2) }}</span>
                    </div>
                    @endif
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Tax:</span>
                        <span>{{ number_format((float)$order->tax_amount, 2) }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Total:</span>
                        <span>{{ number_format((float)$order->total_amount, 2) }}</span>
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
                            <th class="text-end">Disc %</th>
                            <th class="text-end">Tax</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Fulfilled</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product?->name }}</td>
                            <td>{{ $item->warehouse?->name }}</td>
                            <td class="text-end">{{ number_format((float)$item->quantity, 4) }}</td>
                            <td class="text-end">{{ number_format((float)$item->unit_price, 2) }}</td>
                            <td class="text-end">{{ number_format((float)$item->discount_percent, 2) }}%</td>
                            <td class="text-end">{{ number_format((float)$item->tax_amount, 2) }}</td>
                            <td class="text-end">{{ number_format((float)$item->total, 2) }}</td>
                            <td class="text-end">
                                <span class="{{ (float)$item->quantity_fulfilled >= (float)$item->quantity ? 'text-success' : '' }}">
                                    {{ number_format((float)$item->quantity_fulfilled, 4) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Fulfill Modal --}}
    @if($showFulfillModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color:rgba(0,0,0,.5)">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Fulfill Items</h6>
                    <button type="button" class="btn-close" wire:click="$set('showFulfillModal', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Remaining</th>
                                    <th style="width:200px">Qty to Fulfill</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fulfillItems as $i => $fi)
                                @php $soItem = $order->items->firstWhere('id', $fi['sales_order_item_id']); @endphp
                                <tr>
                                    <td>{{ $soItem?->product?->name }}</td>
                                    <td class="text-end">{{ number_format($fi['max'], 4) }}</td>
                                    <td>
                                        <input type="number" step="0.0001" max="{{ $fi['max'] }}"
                                               wire:model="fulfillItems.{{ $i }}.quantity_fulfilled"
                                               class="form-control form-control-sm">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="$set('showFulfillModal', false)">Cancel</button>
                    <button type="button" class="btn btn-primary btn-wave" wire:click="fulfillItems" wire:loading.attr="disabled">
                        <span wire:loading wire:target="fulfillItems" class="spinner-border spinner-border-sm me-1"></span>
                        Confirm Fulfillment
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
                    <h6 class="modal-title">Cancel Sales Order</h6>
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
