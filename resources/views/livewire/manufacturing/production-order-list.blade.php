<div>
    @section('title', 'Production Orders')

    <x-page-header title="Production Orders" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Production Orders'],
    ]">
        <x-slot:actions>
            @can('production.create')
                <a href="{{ route('manufacturing.orders.create') }}" class="btn btn-primary btn-wave" wire:navigate>
                    <i class="ri-add-line me-1"></i> New Order
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card custom-card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex gap-2 flex-wrap">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" style="width:220px" placeholder="Search orders...">
                <select wire:model.live="statusFilter" class="form-select form-select-sm" style="width:150px">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <select wire:model.live="priorityFilter" class="form-select form-select-sm" style="width:130px">
                    <option value="">All Priority</option>
                    <option value="low">Low</option>
                    <option value="normal">Normal</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Product</th>
                            <th>Warehouse</th>
                            <th class="text-end">Planned Qty</th>
                            <th class="text-end">Completed</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr wire:key="order-{{ $order->id }}">
                            <td><code>{{ $order->order_number }}</code></td>
                            <td>
                                <span class="fw-medium">{{ $order->product?->name }}</span>
                            </td>
                            <td>{{ $order->warehouse?->name }}</td>
                            <td class="text-end">{{ number_format((float) $order->planned_quantity, 2) }}</td>
                            <td class="text-end">{{ number_format((float) $order->completed_quantity, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ match($order->priority) { 'urgent' => 'danger', 'high' => 'warning', 'normal' => 'info', default => 'secondary' } }}-transparent">
                                    {{ ucfirst($order->priority) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ match($order->status) { 'completed' => 'success', 'in_progress' => 'primary', 'confirmed' => 'info', 'draft' => 'warning', default => 'danger' } }}-transparent">
                                    {{ str_replace('_', ' ', ucfirst($order->status)) }}
                                </span>
                            </td>
                            <td>{{ format_date($order->created_at) }}</td>
                            <td class="text-end">
                                <a href="{{ route('manufacturing.orders.show', $order) }}"
                                   class="btn btn-sm btn-outline-primary btn-wave" wire:navigate>
                                    <i class="ri-eye-line"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No production orders found.</td>
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
