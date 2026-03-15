<div>
    @section('title', 'Fiscal Years')

    <x-page-header title="Fiscal Years" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Fiscal Years'],
    ]">
        <x-slot:actions>
            <button type="button" class="btn btn-primary btn-wave" wire:click="openCreateModal">
                <i class="ri-add-line me-1"></i> New Fiscal Year
            </button>
        </x-slot:actions>
    </x-page-header>

    <div class="card custom-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Journal Entries</th>
                            <th>Status</th>
                            <th>Closed At</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fiscalYears as $fy)
                        <tr wire:key="fy-{{ $fy->id }}">
                            <td class="fw-medium">{{ $fy->name }}</td>
                            <td>{{ $fy->start_date->format('d M Y') }}</td>
                            <td>{{ $fy->end_date->format('d M Y') }}</td>
                            <td>{{ $fy->journal_entries_count }}</td>
                            <td>
                                @php
                                    $statusColors = ['open' => 'success', 'closed' => 'warning', 'locked' => 'danger'];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$fy->status] ?? 'secondary' }}-transparent">{{ ucfirst($fy->status) }}</span>
                            </td>
                            <td>{{ $fy->closed_at ? $fy->closed_at->format('d M Y H:i') : '—' }}</td>
                            <td class="text-end">
                                @if($fy->status === 'open')
                                    <button class="btn btn-sm btn-outline-warning btn-wave"
                                            wire:click="closeFiscalYear({{ $fy->id }})"
                                            wire:confirm="Are you sure you want to close fiscal year '{{ $fy->name }}'? This will create closing entries and prevent further posting.">
                                        <i class="ri-lock-line me-1"></i> Close
                                    </button>
                                @else
                                    <span class="text-muted fs-11">Closed</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No fiscal years found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Create Fiscal Year Modal --}}
    @if($showCreateModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color:rgba(0,0,0,0.5)">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">New Fiscal Year</h6>
                    <button type="button" class="btn-close" wire:click="$set('showCreateModal', false)"></button>
                </div>
                <form wire:submit="createFiscalYear">
                    <div class="modal-body">
                        <div class="row gy-3">
                            <div class="col-12">
                                <label class="form-label">Name <span class="text-danger">*</span>
                                    <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Descriptive name for this fiscal period (e.g. FY 2026)"></i>
                                </label>
                                <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Start Date <span class="text-danger">*</span>
                                    <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="First day of the fiscal period"></i>
                                </label>
                                <input type="date" wire:model="start_date" class="form-control @error('start_date') is-invalid @enderror">
                                @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Date <span class="text-danger">*</span>
                                    <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Last day of the fiscal period"></i>
                                </label>
                                <input type="date" wire:model="end_date" class="form-control @error('end_date') is-invalid @enderror">
                                @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="$set('showCreateModal', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-wave">
                            <span wire:loading.remove wire:target="createFiscalYear">Create</span>
                            <span wire:loading wire:target="createFiscalYear">Creating...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
