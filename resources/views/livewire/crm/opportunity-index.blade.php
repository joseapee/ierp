<div>
    @section('title', 'Opportunities')

    <x-page-header title="Opportunities" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Opportunities'],
    ]">
        <x-slot:actions>
            @can('opportunities.create')
                <a href="{{ route('crm.opportunities.create') }}" class="btn btn-primary btn-wave" wire:navigate>
                    <i class="ri-add-line me-1"></i> New Opportunity
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card custom-card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex gap-2 flex-wrap">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" style="width:220px" placeholder="Search opportunities...">
                <select wire:model.live="customerFilter" class="form-select form-select-sm" style="width:180px">
                    <option value="">All Customers</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="stageFilter" class="form-select form-select-sm" style="width:180px">
                    <option value="">All Stages</option>
                    @foreach($stages as $stage)
                        <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                    @endforeach
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
                            <th>Name</th>
                            <th>Customer</th>
                            <th>Stage</th>
                            <th class="text-end">Value</th>
                            <th class="text-end">Probability</th>
                            <th>Close Date</th>
                            <th>Assigned To</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($opportunities as $opp)
                        <tr wire:key="opp-{{ $opp->id }}">
                            <td class="fw-medium">
                                <a href="{{ route('crm.opportunities.show', $opp) }}" wire:navigate class="text-primary">{{ $opp->name }}</a>
                            </td>
                            <td>{{ $opp->customer?->name ?? '—' }}</td>
                            <td>
                                @if($opp->pipelineStage)
                                <span class="badge" style="background-color: {{ $opp->pipelineStage->color }}20; color: {{ $opp->pipelineStage->color }};">
                                    {{ $opp->pipelineStage->name }}
                                </span>
                                @endif
                            </td>
                            <td class="text-end">{{ number_format((float)$opp->expected_value, 2) }}</td>
                            <td class="text-end">{{ number_format((float)$opp->probability, 0) }}%</td>
                            <td>{{ $opp->expected_close_date?->format('Y-m-d') ?? '—' }}</td>
                            <td>{{ $opp->assignedUser?->name ?? '—' }}</td>
                            <td class="text-end">
                                @can('opportunities.edit')
                                <a href="{{ route('crm.opportunities.edit', $opp) }}"
                                   class="btn btn-sm btn-outline-primary btn-wave" wire:navigate>
                                    <i class="ri-pencil-line"></i>
                                </a>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No opportunities found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($opportunities->hasPages())
        <div class="card-footer">
            {{ $opportunities->links() }}
        </div>
        @endif
    </div>
</div>
