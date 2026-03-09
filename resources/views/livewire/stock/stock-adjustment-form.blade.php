<div>
    @section('title', 'New Adjustment')

    <x-page-header title="New Stock Adjustment" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Adjustments', 'route' => 'stock.adjustments.index'],
        ['label' => 'New'],
    ]" />

    <form wire:submit="save">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Adjustment Details</div>
            </div>
            <div class="card-body">
                <div class="row gy-3">
                    <div class="col-md-4">
                        <label class="form-label">Adjustment # <span class="text-danger">*</span></label>
                        <input type="text" wire:model="adjustment_number" class="form-control @error('adjustment_number') is-invalid @enderror">
                        @error('adjustment_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Warehouse <span class="text-danger">*</span></label>
                        <select wire:model="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror">
                            <option value="">— Select —</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                            @endforeach
                        </select>
                        @error('warehouse_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <input type="text" wire:model="reason" class="form-control @error('reason') is-invalid @enderror">
                        @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea wire:model="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">Items</div>
                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addItem">
                    <i class="ri-add-line me-1"></i> Add Item
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width:30%">Product</th>
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>Unit Cost</th>
                                <th>Reason</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $i => $item)
                            <tr wire:key="item-{{ $i }}">
                                <td>
                                    <select wire:model="items.{{ $i }}.product_id" class="form-select form-select-sm @error('items.'.$i.'.product_id') is-invalid @enderror">
                                        <option value="">— Select —</option>
                                        @foreach($products as $prod)
                                            <option value="{{ $prod->id }}">{{ $prod->name }} ({{ $prod->sku }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select wire:model="items.{{ $i }}.type" class="form-select form-select-sm">
                                        <option value="addition">Addition</option>
                                        <option value="subtraction">Subtraction</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.01" wire:model="items.{{ $i }}.quantity" class="form-control form-control-sm @error('items.'.$i.'.quantity') is-invalid @enderror">
                                </td>
                                <td>
                                    <input type="number" step="0.0001" wire:model="items.{{ $i }}.unit_cost" class="form-control form-control-sm @error('items.'.$i.'.unit_cost') is-invalid @enderror">
                                </td>
                                <td>
                                    <input type="text" wire:model="items.{{ $i }}.reason" class="form-control form-control-sm">
                                </td>
                                <td>
                                    @if(count($items) > 1)
                                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeItem({{ $i }})">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="{{ route('stock.adjustments.index') }}" class="btn btn-light" wire:navigate>Cancel</a>
            <button type="submit" class="btn btn-primary btn-wave">
                <span wire:loading.remove wire:target="save">Create Adjustment</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </form>
</div>
