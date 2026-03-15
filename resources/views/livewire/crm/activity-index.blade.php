<div>
    @section('title', 'Activities')

    <x-page-header title="Activities" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Activities'],
    ]" />

    <div class="card custom-card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex gap-2 flex-wrap">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" style="width:220px" placeholder="Search activities...">
                <select wire:model.live="typeFilter" class="form-select form-select-sm" style="width:150px">
                    <option value="">All Types</option>
                    <option value="call">Call</option>
                    <option value="meeting">Meeting</option>
                    <option value="email">Email</option>
                    <option value="follow_up">Follow Up</option>
                    <option value="demo">Demo</option>
                    <option value="site_visit">Site Visit</option>
                </select>
                <select wire:model.live="statusFilter" class="form-select form-select-sm" style="width:140px">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <select wire:model.live="assignedToFilter" class="form-select form-select-sm" style="width:160px">
                    <option value="">All Assigned</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Assigned To</th>
                            <th>Completed At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $activity)
                        <tr wire:key="activity-{{ $activity->id }}">
                            <td class="fw-medium">{{ $activity->subject }}</td>
                            <td>
                                <span class="badge bg-secondary-transparent">{{ str_replace('_', ' ', ucfirst($activity->type)) }}</span>
                            </td>
                            <td>
                                @php
                                    $actStatusColors = ['pending' => 'warning', 'completed' => 'success', 'cancelled' => 'danger'];
                                @endphp
                                <span class="badge bg-{{ $actStatusColors[$activity->status] ?? 'secondary' }}-transparent">
                                    {{ ucfirst($activity->status) }}
                                </span>
                            </td>
                            <td>
                                @if($activity->due_date)
                                    <span class="{{ $activity->status === 'pending' && $activity->due_date->isPast() ? 'text-danger fw-bold' : '' }}">
                                        {{ $activity->due_date->format('Y-m-d H:i') }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $activity->assignedUser?->name ?? '—' }}</td>
                            <td>{{ $activity->completed_at?->format('Y-m-d H:i') ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No activities found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($activities->hasPages())
        <div class="card-footer">
            {{ $activities->links() }}
        </div>
        @endif
    </div>
</div>
