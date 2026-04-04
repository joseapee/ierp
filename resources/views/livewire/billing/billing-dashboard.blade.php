<div>
    @section('title', 'Billing')

    <x-page-header title="Billing & Subscription" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Billing'],
    ]" />

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- Current Plan Card --}}
        <div class="col-xl-4">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Current Plan</div>
                </div>
                <div class="card-body">
                    @if($currentPlan)
                        <h4 class="fw-semibold mb-1">{{ $currentPlan->name }}</h4>
                        @if($currentPlan->description)
                            <p class="text-muted fs-12 mb-3">{{ $currentPlan->description }}</p>
                        @endif
                        @if($subscription)
                            <div class="mb-2">
                                <span class="badge
                                    @switch($subscription->status)
                                        @case('active') bg-success-transparent @break
                                        @case('trial') bg-info-transparent @break
                                        @case('past_due') bg-warning-transparent @break
                                        @case('grace_period') bg-warning-transparent @break
                                        @case('suspended') bg-danger-transparent @break
                                        @case('cancelled') bg-danger-transparent @break
                                        @default bg-secondary-transparent
                                    @endswitch
                                    ">{{ ucfirst(str_replace('_', ' ', $subscription->status)) }}</span>
                                <span class="badge bg-primary-transparent ms-1">{{ ucfirst($subscription->billing_cycle) }}</span>
                            </div>
                            <ul class="list-unstyled fs-13 text-muted mb-0">
                                @if($subscription->isTrial() && $subscription->trial_ends_at)
                                    <li class="mb-1">Trial ends: <strong>{{ format_date($subscription->trial_ends_at, 'M d, Y') }}</strong></li>
                                @endif
                                <li class="mb-1">
                                    @if($subscription->billing_cycle === 'annual')
                                        Price: <strong>{{ format_currency((float) $currentPlan->annual_price) }}/yr</strong>
                                    @else
                                        Price: <strong>{{ format_currency((float) $currentPlan->monthly_price) }}/mo</strong>
                                    @endif
                                </li>
                                @if($subscription->ends_at)
                                    <li class="mb-1">Next billing: <strong>{{ format_date($subscription->ends_at, 'M d, Y') }}</strong></li>
                                @endif
                                <li>Days remaining: <strong>{{ $subscription->daysRemaining() }}</strong></li>
                            </ul>
                        @else
                            <p class="text-muted">No active subscription.</p>
                        @endif
                    @else
                        <p class="text-muted">No plan selected.</p>
                    @endif
                </div>
                @if($subscription && $subscription->isActive() && $subscription->status !== 'cancelled')
                    <div class="card-footer d-flex gap-2 flex-wrap">
                        @if($subscription->status === 'active')
                            <button class="btn btn-sm btn-primary btn-wave"
                                    wire:click="initiateRenewal"
                                    wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="initiateRenewal">
                                    <i class="ri-refresh-line me-1"></i> Renew Now
                                </span>
                                <span wire:loading wire:target="initiateRenewal">
                                    <i class="ri-loader-4-line me-1 spin"></i> Processing...
                                </span>
                            </button>
                        @endif
                        <button class="btn btn-sm btn-outline-danger btn-wave"
                                wire:click="cancelSubscription"
                                wire:confirm="Are you sure you want to cancel your subscription?">
                            Cancel Subscription
                        </button>
                    </div>
                @endif
            </div>

            {{-- Plan Features --}}
            @if($currentPlan && $currentPlan->features->isNotEmpty())
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">Plan Features</div>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @foreach($currentPlan->features as $feature)
                                <li class="list-group-item d-flex justify-content-between align-items-center fs-13">
                                    <span>{{ ucwords(str_replace('_', ' ', $feature->feature_key)) }}</span>
                                    @if(in_array($feature->feature_value, ['true', 'false']))
                                        @if($feature->feature_value === 'true')
                                            <span class="badge bg-success-transparent">Enabled</span>
                                        @else
                                            <span class="badge bg-danger-transparent">Disabled</span>
                                        @endif
                                    @elseif($feature->feature_value === 'unlimited')
                                        <span class="badge bg-primary-transparent">Unlimited</span>
                                    @else
                                        <span class="badge bg-secondary-transparent">{{ $feature->feature_value }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>

        {{-- Available Plans --}}
        <div class="col-xl-8">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Available Plans</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($plans as $plan)
                            <div class="col-md-6 col-lg-6 mb-3" wire:key="plan-{{ $plan->id }}">
                                <div class="card border {{ $currentPlan && $currentPlan->id === $plan->id ? 'border-primary' : '' }}">
                                    <div class="card-body text-center p-3">
                                        <h6 class="fw-semibold">{{ $plan->name }}</h6>
                                        <div class="mb-2">
                                            <span class="fs-20 fw-bold">{{ format_currency((float) $plan->monthly_price, 0) }}</span>
                                            <span class="text-muted fs-12">/mo</span>
                                        </div>
                                        <div class="text-muted fs-12 mb-3">
                                            {{ format_currency((float) $plan->annual_price, 0) }}/yr
                                        </div>
                                        @if($currentPlan && $currentPlan->id === $plan->id)
                                            <span class="badge bg-primary-transparent">Current Plan</span>
                                        @else
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-sm btn-outline-primary btn-wave"
                                                        wire:click="initiatePlanChange({{ $plan->id }}, 'monthly')">
                                                    Monthly
                                                </button>
                                                <button class="btn btn-sm btn-outline-primary btn-wave"
                                                        wire:click="initiatePlanChange({{ $plan->id }}, 'annual')">
                                                    Annual
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Payment History --}}
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Payment History</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Reference</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $payment)
                                    <tr wire:key="payment-{{ $payment->id }}">
                                        <td>{{ format_date($payment->created_at, 'M d, Y') }}</td>
                                        <td>{{ $payment->currency }} {{ format_money((float) $payment->amount) }}</td>
                                        <td>{{ ucfirst($payment->payment_method ?? '-') }}</td>
                                        <td>
                                            <span class="badge {{ $payment->status === 'success' ? 'bg-success-transparent' : 'bg-danger-transparent' }}">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                        </td>
                                        <td class="fs-12 text-muted">{{ $payment->paystack_reference ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No payments yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Invoices --}}
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Invoices</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoices as $invoice)
                                    <tr wire:key="invoice-{{ $invoice->id }}">
                                        <td>{{ $invoice->invoice_number }}</td>
                                        <td>{{ $invoice->issued_at ? format_date($invoice->issued_at, 'M d, Y') : format_date($invoice->created_at, 'M d, Y') }}</td>
                                        <td>{{ $invoice->currency }} {{ format_money((float) $invoice->amount) }}</td>
                                        <td>
                                            <span class="badge {{ $invoice->status === 'paid' ? 'bg-success-transparent' : 'bg-warning-transparent' }}">
                                                {{ ucfirst($invoice->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No invoices yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
