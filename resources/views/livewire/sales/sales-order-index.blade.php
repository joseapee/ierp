<div>
    @section('title', 'Sales Orders')

    <x-page-header title="Sales Orders" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Sales Orders'],
    ]">
        <x-slot:actions>
            @can('sales-orders.create')
                <a href="{{ route('sales.orders.create') }}" class="btn btn-primary btn-wave" wire:navigate>
                    <i class="ri-add-line me-1"></i> New Sales Order
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
                    <option value="partially_fulfilled">Partially Fulfilled</option>
                    <option value="fulfilled">Fulfilled</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <select wire:model.live="customerFilter" class="form-select form-select-sm" style="width:180px">
                    <option value="">All Customers</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
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
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr wire:key="so-{{ $order->id }}">
                            <td><code>{{ $order->order_number }}</code></td>
                            <td>{{ $order->customer?->name }}</td>
                            <td>{{ format_date($order->order_date) }}</td>
                            <td>
                                @php
                                    $colors = ['draft' => 'warning', 'confirmed' => 'info', 'partially_fulfilled' => 'primary', 'fulfilled' => 'success', 'cancelled' => 'danger'];
                                @endphp
                                <span class="badge bg-{{ $colors[$order->status] ?? 'secondary' }}-transparent">
                                    {{ str_replace('_', ' ', ucfirst($order->status)) }}
                                </span>
                            </td>
                            <td class="text-end">{{ format_currency($order->total_amount) }}</td>
                            <td class="text-end">
                                <a href="{{ route('sales.orders.show', $order) }}"
                                   class="btn btn-sm btn-outline-primary btn-wave" wire:navigate>
                                    <i class="ri-eye-line"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No sales orders found.</td>
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
