<div>
    @section('title', 'New Transfer')

    <x-page-header title="New Stock Transfer" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Transfers', 'route' => 'stock.transfers.index'],
        ['label' => 'New'],
    ]" />

    <form wire:submit="save">
        <div class="card custom-card">
            <div class="card-header"><div class="card-title">Transfer Details</div></div>
            <div class="card-body">
                <div class="row gy-3">
                    <div class="col-md-4">
                        <label class="form-label">Transfer # <span class="text-danger">*</span></label>
                        <input type="text" wire:model="transfer_number" class="form-control @error('transfer_number') is-invalid @enderror">
                        @error('transfer_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">From Warehouse <span class="text-danger">*</span></label>
                        <select wire:model="from_warehouse_id" class="form-select @error('from_warehouse_id') is-invalid @enderror">
                            <option value="">— Select —</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                            @endforeach
                        </select>
                        @error('from_warehouse_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">To Warehouse <span class="text-danger">*</span></label>
                        <select wire:model="to_warehouse_id" class="form-select @error('to_warehouse_id') is-invalid @enderror">
                            <option value="">— Select —</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                            @endforeach
                        </select>
                        @error('to_warehouse_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                <th style="width:40%">Product</th>
                                <th>Quantity</th>
                                <th>Unit Cost</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $i => $item)
                            <tr wire:key="titem-{{ $i }}">
                                <td>
                                    <select wire:model="items.{{ $i }}.product_id" class="form-select form-select-sm @error('items.'.$i.'.product_id') is-invalid @enderror">
                                        <option value="">— Select —</option>
                                        @foreach($products as $prod)
                                            <option value="{{ $prod->id }}">{{ $prod->name }} ({{ $prod->sku }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.01" wire:model="items.{{ $i }}.quantity" class="form-control form-control-sm @error('items.'.$i.'.quantity') is-invalid @enderror">
                                </td>
                                <td>
                                    <input type="number" step="0.0001" wire:model="items.{{ $i }}.unit_cost" class="form-control form-control-sm @error('items.'.$i.'.unit_cost') is-invalid @enderror">
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
            <a href="{{ route('stock.transfers.index') }}" class="btn btn-light" wire:navigate>Cancel</a>
            <button type="submit" class="btn btn-primary btn-wave">
                <span wire:loading.remove wire:target="save">Create Transfer</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </form>
</div>
