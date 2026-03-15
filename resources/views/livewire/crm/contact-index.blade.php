<div>
    @section('title', 'Contacts')

    <x-page-header title="Contacts" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Contacts'],
    ]">
        <x-slot:actions>
            @can('crm-contacts.create')
                <a href="{{ route('crm.contacts.create') }}" class="btn btn-primary btn-wave" wire:navigate>
                    <i class="ri-add-line me-1"></i> New Contact
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card custom-card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex gap-2 flex-wrap">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" style="width:220px" placeholder="Search contacts...">
                <select wire:model.live="customerFilter" class="form-select form-select-sm" style="width:200px">
                    <option value="">All Companies</option>
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
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Company</th>
                            <th>Job Title</th>
                            <th>Primary</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contacts as $contact)
                        <tr wire:key="contact-{{ $contact->id }}">
                            <td class="fw-medium">{{ $contact->full_name }}</td>
                            <td>{{ $contact->email ?? '—' }}</td>
                            <td>{{ $contact->phone ?? '—' }}</td>
                            <td>{{ $contact->customer?->name ?? '—' }}</td>
                            <td>{{ $contact->job_title ?? '—' }}</td>
                            <td>
                                @if($contact->is_primary)
                                    <span class="badge bg-success-transparent">Primary</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @can('crm-contacts.edit')
                                <a href="{{ route('crm.contacts.edit', $contact) }}"
                                   class="btn btn-sm btn-outline-primary btn-wave" wire:navigate>
                                    <i class="ri-pencil-line"></i>
                                </a>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No contacts found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($contacts->hasPages())
        <div class="card-footer">
            {{ $contacts->links() }}
        </div>
        @endif
    </div>
</div>
