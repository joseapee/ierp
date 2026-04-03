<div>
    @section('title', 'Unit Conversions')

    <x-page-header title="Unit Conversions" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Units of Measure', 'route' => 'units.index'],
        ['label' => 'Conversions'],
    ]">
        <x-slot:actions>
            @can('units.create')
                <button class="btn btn-primary btn-wave"
                        wire:click="$dispatch('openConversionFormModal')">
                    <i class="ri-add-line me-1"></i> Add Conversion
                </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card custom-card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex gap-2 flex-wrap">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" style="width:220px" placeholder="Search conversions...">
                <select wire:model.live="typeFilter" class="form-select form-select-sm" style="width:140px">
                    <option value="">All Types</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>From Unit</th>
                            <th>To Unit</th>
                            <th>Type</th>
                            <th>Factor</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($conversions as $conversion)
                        <tr wire:key="conversion-{{ $conversion->id }}">
                            <td>
                                {{ $conversion->fromUnit->name }}
                                <code class="ms-1">{{ $conversion->fromUnit->abbreviation }}</code>
                            </td>
                            <td>
                                {{ $conversion->toUnit->name }}
                                <code class="ms-1">{{ $conversion->toUnit->abbreviation }}</code>
                            </td>
                            <td><span class="badge bg-info-transparent">{{ ucfirst($conversion->fromUnit->type) }}</span></td>
                            <td>
                                <span class="fw-medium">{{ rtrim(rtrim(number_format((float) $conversion->factor, 10), '0'), '.') }}</span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    @can('units.edit')
                                    <button class="btn btn-sm btn-outline-info btn-wave"
                                            wire:click="$dispatch('openConversionFormModal', { conversionId: {{ $conversion->id }} })">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    @endcan
                                    @can('units.delete')
                                    <button class="btn btn-sm btn-outline-danger btn-wave"
                                            wire:click="deleteConversion({{ $conversion->id }})"
                                            wire:confirm="Are you sure you want to delete this conversion?">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No conversions found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($conversions->hasPages())
        <div class="card-footer">
            {{ $conversions->links() }}
        </div>
        @endif
    </div>

    <livewire:catalog.unit-conversion-form-modal />
</div>
