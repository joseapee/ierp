<div>
    @section('title', 'Pipeline Board')

    <x-page-header title="Pipeline Board" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Pipeline Board'],
    ]" />

    {{-- Filter --}}
    <div class="card custom-card mb-3">
        <div class="card-body py-2">
            <div class="d-flex align-items-center gap-3">
                <label class="form-label mb-0 fw-medium">Assigned To:</label>
                <select wire:model.live="assignedToFilter" class="form-select form-select-sm" style="width:220px">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if(empty($boardData))
        <div class="alert alert-secondary">
            No pipeline stages configured. <a href="{{ route('crm.pipeline.stages') }}" wire:navigate>Manage stages</a>.
        </div>
    @else
        <div class="row g-3" style="overflow-x: auto; flex-wrap: nowrap;">
            @foreach($boardData as $column)
            <div class="col" style="min-width: 260px;">
                <div class="card custom-card h-100">
                    <div class="card-header" style="background-color: {{ $column['stage']->color }}15;">
                        <div class="card-title mb-0 d-flex align-items-center gap-2">
                            <span class="d-inline-block rounded-circle" style="width:10px;height:10px;background:{{ $column['stage']->color }}"></span>
                            {{ $column['stage']->name }}
                            <span class="badge bg-primary-transparent ms-auto">{{ $column['opportunities']->count() }}</span>
                        </div>
                    </div>
                    <div class="card-body p-2" style="min-height: 200px;">
                        @foreach($column['opportunities'] as $opp)
                        <div class="card mb-2 border shadow-sm" wire:key="board-opp-{{ $opp->id }}">
                            <div class="card-body p-2">
                                <a href="{{ route('crm.opportunities.show', $opp) }}" wire:navigate class="fw-medium small text-primary d-block">{{ $opp->name }}</a>
                                <div class="text-muted small">{{ $opp->customer?->name }}</div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span class="fw-bold small">{{ format_currency((float)$opp->expected_value, 0) }}</span>
                                    @if($opp->assignedUser)
                                        <small class="text-muted">{{ $opp->assignedUser->name }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
