<div>
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color:rgba(0,0,0,0.5)">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">{{ $conversionId ? 'Edit Conversion' : 'Add Conversion' }}</h6>
                    <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                </div>
                <form wire:submit="save">
                    <div class="modal-body">
                        <div class="row gy-3">
                            <div class="col-md-6">
                                <label class="form-label">From Unit <span class="text-danger">*</span></label>
                                <select wire:model="from_unit_id" class="form-select @error('from_unit_id') is-invalid @enderror">
                                    <option value="">Select unit...</option>
                                    @foreach($units->groupBy('type') as $type => $groupedUnits)
                                        <optgroup label="{{ ucfirst($type) }}">
                                            @foreach($groupedUnits as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->abbreviation }})</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('from_unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">To Unit <span class="text-danger">*</span></label>
                                <select wire:model="to_unit_id" class="form-select @error('to_unit_id') is-invalid @enderror">
                                    <option value="">Select unit...</option>
                                    @foreach($units->groupBy('type') as $type => $groupedUnits)
                                        <optgroup label="{{ ucfirst($type) }}">
                                            @foreach($groupedUnits as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->abbreviation }})</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('to_unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Conversion Factor <span class="text-danger">*</span></label>
                                <input type="number" step="any" min="0" wire:model="factor" class="form-control @error('factor') is-invalid @enderror" placeholder="e.g. 1000 (1 kg = 1000 g)">
                                @error('factor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="form-text">How many target units equal one source unit.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="$set('showModal', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-wave">
                            <span wire:loading.remove wire:target="save">{{ $conversionId ? 'Update' : 'Create' }}</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
