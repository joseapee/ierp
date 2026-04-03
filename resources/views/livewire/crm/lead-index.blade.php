<div>
    @section('title', 'Leads')

    <x-page-header title="Leads" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Leads'],
    ]">
        <x-slot:actions>
            @can('leads.create')
                <a href="{{ route('crm.leads.create') }}" class="btn btn-primary btn-wave" wire:navigate>
                    <i class="ri-add-line me-1"></i> New Lead
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card custom-card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex gap-2 flex-wrap">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" style="width:220px" placeholder="Search leads...">
                <select wire:model.live="statusFilter" class="form-select form-select-sm" style="width:150px">
                    <option value="">All Status</option>
                    <option value="new">New</option>
                    <option value="contacted">Contacted</option>
                    <option value="qualified">Qualified</option>
                    <option value="proposal_sent">Proposal Sent</option>
                    <option value="negotiation">Negotiation</option>
                    <option value="converted">Converted</option>
                    <option value="lost">Lost</option>
                </select>
                <select wire:model.live="sourceFilter" class="form-select form-select-sm" style="width:150px">
                    <option value="">All Sources</option>
                    <option value="website">Website</option>
                    <option value="social_media">Social Media</option>
                    <option value="email_campaign">Email Campaign</option>
                    <option value="phone_inquiry">Phone Inquiry</option>
                    <option value="walk_in">Walk-in</option>
                    <option value="referral">Referral</option>
                    <option value="manual">Manual</option>
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
                            <th>Company</th>
                            <th>Email</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th class="text-end">Est. Value</th>
                            <th>Assigned To</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leads as $lead)
                        <tr wire:key="lead-{{ $lead->id }}">
                            <td class="fw-medium">
                                <a href="{{ route('crm.leads.show', $lead) }}" wire:navigate class="text-primary">{{ $lead->lead_name }}</a>
                            </td>
                            <td>{{ $lead->company_name ?? '—' }}</td>
                            <td>{{ $lead->email ?? '—' }}</td>
                            <td>
                                <span class="badge bg-secondary-transparent">{{ str_replace('_', ' ', ucfirst($lead->source ?? '')) }}</span>
                            </td>
                            <td>
                                @php
                                    $statusColors = ['new' => 'info', 'contacted' => 'primary', 'qualified' => 'warning', 'proposal_sent' => 'secondary', 'negotiation' => 'dark', 'converted' => 'success', 'lost' => 'danger'];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$lead->status] ?? 'secondary' }}-transparent">
                                    {{ str_replace('_', ' ', ucfirst($lead->status)) }}
                                </span>
                            </td>
                            <td class="text-end">{{ format_currency((float)$lead->estimated_value) }}</td>
                            <td>{{ $lead->assignedUser?->name ?? '—' }}</td>
                            <td class="text-end">
                                @can('leads.edit')
                                <a href="{{ route('crm.leads.edit', $lead) }}"
                                   class="btn btn-sm btn-outline-primary btn-wave" wire:navigate>
                                    <i class="ri-pencil-line"></i>
                                </a>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No leads found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($leads->hasPages())
        <div class="card-footer">
            {{ $leads->links() }}
        </div>
        @endif
    </div>
</div>
