<div>
    @section('title', 'Purchase Orders')

    <x-page-header title="Purchase Orders" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Purchase Orders'],
    ]">
        <x-slot:actions>
            @can('purchase-orders.create')
                <a href="{{ route('procurement.purchase-orders.create') }}" class="btn btn-primary btn-wave" wire:navigate>
                    <i class="ri-add-line me-1"></i> New Purchase Order
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card custom-card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex gap-2 flex-wrap">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" style="width:220px" placeholder="Search orders...">
                <select wire:model.live="statusFilter" class="form-select form-select-sm" style="width:160px">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="partially_received">Partially Received</option>
                    <option value="received">Received</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <select wire:model.live="supplierFilter" class="form-select form-select-sm" style="width:180px">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Supplier</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr wire:key="po-{{ $order->id }}">
                            <td><code>{{ $order->order_number }}</code></td>
                            <td>{{ $order->supplier?->name }}</td>
                            <td>{{ $order->order_date?->format('Y-m-d') }}</td>
                            <td>
                                @php
                                    $colors = ['draft' => 'warning', 'confirmed' => 'info', 'partially_received' => 'primary', 'received' => 'success', 'cancelled' => 'danger'];
                                @endphp
                                <span class="badge bg-{{ $colors[$order->status] ?? 'secondary' }}-transparent">
                                    {{ str_replace('_', ' ', ucfirst($order->status)) }}
                                </span>
                            </td>
                            <td class="text-end">{{ number_format((float)$order->total_amount, 2) }}</td>
                            <td class="text-end">
                                <a href="{{ route('procurement.purchase-orders.show', $order) }}"
                                   class="btn btn-sm btn-outline-primary btn-wave" wire:navigate>
                                    <i class="ri-eye-line"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No purchase orders found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($orders->hasPages())
        <div class="card-footer">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>
