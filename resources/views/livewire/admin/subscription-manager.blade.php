<div>
    @section('title', 'Subscriptions')

    <x-page-header title="Subscription Management" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Subscriptions'],
    ]" />

    <div class="card custom-card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex gap-2 flex-wrap">
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       class="form-control form-control-sm"
                       style="width:220px"
                       placeholder="Search by tenant...">

                <select wire:model.live="statusFilter"
                        class="form-select form-select-sm"
                        style="width:160px">
                    <option value="">All Statuses</option>
                    <option value="trial">Trial</option>
                    <option value="active">Active</option>
                    <option value="past_due">Past Due</option>
                    <option value="grace_period">Grace Period</option>
                    <option value="suspended">Suspended</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>Tenant</th>
                            <th>Plan</th>
                            <th>Cycle</th>
                            <th>Status</th>
                            <th>Starts</th>
                            <th>Ends</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscriptions as $sub)
                        <tr wire:key="sub-{{ $sub->id }}">
                            <td>
                                <span class="fw-semibold">{{ $sub->tenant?->name ?? 'N/A' }}</span>
                            </td>
                            <td>{{ $sub->plan?->name ?? 'N/A' }}</td>
                            <td>{{ ucfirst($sub->billing_cycle) }}</td>
                            <td>
                                <span class="badge
                                    @switch($sub->status)
                                        @case('active') bg-success-transparent @break
                                        @case('trial') bg-info-transparent @break
                                        @case('past_due') bg-warning-transparent @break
                                        @case('grace_period') bg-warning-transparent @break
                                        @case('suspended') bg-danger-transparent @break
                                        @case('cancelled') bg-danger-transparent @break
                                        @default bg-secondary-transparent
                                    @endswitch
                                    ">{{ ucfirst(str_replace('_', ' ', $sub->status)) }}</span>
                            </td>
                            <td>{{ $sub->starts_at ? format_date($sub->starts_at, 'M d, Y') : '-' }}</td>
                            <td>{{ $sub->ends_at ? format_date($sub->ends_at, 'M d, Y') : '-' }}</td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    @if($sub->isTrial())
                                        <button class="btn btn-sm btn-outline-info btn-wave"
                                                wire:click="extendTrial({{ $sub->id }})"
                                                data-bs-toggle="tooltip" title="Extend trial by 7 days">
                                            <i class="ri-time-line"></i>
                                        </button>
                                    @endif
                                    @if(in_array($sub->status, ['trial', 'past_due', 'grace_period']))
                                        <button class="btn btn-sm btn-outline-success btn-wave"
                                                wire:click="activateManually({{ $sub->id }})"
                                                wire:confirm="Manually activate this subscription?"
                                                data-bs-toggle="tooltip" title="Activate manually">
                                            <i class="ri-check-line"></i>
                                        </button>
                                    @endif
                                    @if(in_array($sub->status, ['active', 'trial', 'past_due', 'grace_period']))
                                        <button class="btn btn-sm btn-outline-danger btn-wave"
                                                wire:click="suspendSubscription({{ $sub->id }})"
                                                wire:confirm="Suspend this subscription?"
                                                data-bs-toggle="tooltip" title="Suspend">
                                            <i class="ri-forbid-line"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No subscriptions found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($subscriptions->hasPages())
        <div class="card-footer">
            {{ $subscriptions->links() }}
        </div>
        @endif
    </div>
</div>
