<div>
    @section('title', 'Customer: ' . $customer->name)

    <x-page-header :title="'Customer: ' . $customer->name" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Customers', 'route' => 'sales.customers.index'],
        ['label' => $customer->name],
    ]">
        <x-slot:actions>
            @can('customers.edit')
            <a href="{{ route('sales.customers.edit', $customer) }}" class="btn btn-outline-primary btn-wave" wire:navigate>
                <i class="ri-pencil-line me-1"></i> Edit
            </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="row">
        {{-- Customer Info --}}
        <div class="col-lg-4">
            <div class="card custom-card">
                <div class="card-header"><div class="card-title">Customer Information</div></div>
                <div class="card-body">
                    <div class="mb-2"><span class="text-muted">Name:</span><br><strong>{{ $customer->name }}</strong></div>
                    <div class="mb-2"><span class="text-muted">Email:</span><br>{{ $customer->email ?? '—' }}</div>
                    <div class="mb-2"><span class="text-muted">Phone:</span><br>{{ $customer->phone ?? '—' }}</div>
                    <div class="mb-2"><span class="text-muted">Tax ID:</span><br>{{ $customer->tax_id ?? '—' }}</div>
                    <div class="mb-2"><span class="text-muted">Credit Limit:</span><br>{{ format_currency($customer->credit_limit) }}</div>
                    <div class="mb-2"><span class="text-muted">Payment Terms:</span><br>{{ $customer->payment_terms }} days</div>
                    <div class="mb-2">
                        <span class="text-muted">Status:</span><br>
                        <span class="badge bg-{{ $customer->is_active ? 'success' : 'danger' }}-transparent">
                            {{ $customer->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Contacts --}}
            <div class="card custom-card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="card-title mb-0">Contacts</div>
                    @can('crm-contacts.create')
                    <a href="{{ route('crm.contacts.create') }}" class="btn btn-sm btn-outline-primary btn-wave" wire:navigate>
                        <i class="ri-add-line"></i>
                    </a>
                    @endcan
                </div>
                <div class="card-body p-0">
                    @forelse($customer->contacts as $contact)
                    <div class="d-flex align-items-center p-2 border-bottom" wire:key="contact-{{ $contact->id }}">
                        <div class="flex-fill">
                            <div class="fw-medium">
                                {{ $contact->full_name }}
                                @if($contact->is_primary)
                                <span class="badge bg-warning-transparent ms-1">Primary</span>
                                @endif
                            </div>
                            <small class="text-muted">{{ $contact->job_title ?? '' }} {{ $contact->email ? '— '.$contact->email : '' }}</small>
                        </div>
                        @can('crm-contacts.edit')
                        <a href="{{ route('crm.contacts.edit', $contact) }}" class="btn btn-sm btn-icon btn-outline-primary" wire:navigate>
                            <i class="ri-pencil-line"></i>
                        </a>
                        @endcan
                    </div>
                    @empty
                    <div class="p-3 text-center text-muted">No contacts yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right Column: Opportunities, Orders, Communications --}}
        <div class="col-lg-8">
            {{-- Opportunities --}}
            <div class="card custom-card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="card-title mb-0">Opportunities</div>
                    @can('opportunities.create')
                    <a href="{{ route('crm.opportunities.create') }}" class="btn btn-sm btn-outline-primary btn-wave" wire:navigate>
                        <i class="ri-add-line me-1"></i> New
                    </a>
                    @endcan
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Stage</th>
                                    <th class="text-end">Value</th>
                                    <th>Close Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customer->opportunities as $opp)
                                <tr wire:key="opp-{{ $opp->id }}">
                                    <td>
                                        <a href="{{ route('crm.opportunities.show', $opp) }}" wire:navigate class="text-primary fw-medium">{{ $opp->name }}</a>
                                    </td>
                                    <td>
                                        @if($opp->pipelineStage)
                                        <span class="badge" style="background-color: {{ $opp->pipelineStage->color }}20; color: {{ $opp->pipelineStage->color }};">
                                            {{ $opp->pipelineStage->name }}
                                        </span>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ format_currency($opp->expected_value) }}</td>
                                    <td>{{ format_date($opp->expected_close_date) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">No opportunities.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Sales Orders --}}
            <div class="card custom-card">
                <div class="card-header"><div class="card-title">Sales Orders</div></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Status</th>
                                    <th class="text-end">Total</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customer->salesOrders as $order)
                                <tr wire:key="order-{{ $order->id }}">
                                    <td>
                                        <a href="{{ route('sales.orders.show', $order) }}" wire:navigate class="text-primary fw-medium">{{ $order->order_number }}</a>
                                    </td>
                                    <td>
                                        @php
                                            $orderColors = ['draft' => 'secondary', 'confirmed' => 'primary', 'fulfilled' => 'success', 'partially_fulfilled' => 'info', 'cancelled' => 'danger'];
                                        @endphp
                                        <span class="badge bg-{{ $orderColors[$order->status] ?? 'secondary' }}-transparent">
                                            {{ str_replace('_', ' ', ucfirst($order->status)) }}
                                        </span>
                                    </td>
                                    <td class="text-end">{{ format_currency($order->total_amount) }}</td>
                                    <td>{{ format_date($order->order_date ?? $order->created_at) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">No sales orders.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Communication Timeline --}}
            @can('crm-communications.view')
            <livewire:crm.communication-timeline :customerId="$customer->id" />
            @endcan
        </div>
    </div>
</div>
