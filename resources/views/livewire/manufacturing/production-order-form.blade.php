<div>
    @section('title', 'New Production Order')

    <x-page-header title="New Production Order" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Production Orders', 'route' => 'manufacturing.orders.index'],
        ['label' => 'New Order'],
    ]" />

    <div class="card custom-card">
        <div class="card-header">
            <div class="card-title">Order Details</div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Product <span class="text-danger">*</span></label>
                    <select wire:model.live="product_id" class="form-select @error('product_id') is-invalid @enderror">
                        <option value="">Select manufactured product...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                        @endforeach
                    </select>
                    @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Bill of Materials <span class="text-danger">*</span></label>
                    <select wire:model="bom_id" class="form-select @error('bom_id') is-invalid @enderror" {{ empty($availableBoms) ? 'disabled' : '' }}>
                        <option value="">{{ empty($availableBoms) ? 'Select a product first...' : 'Select BOM...' }}</option>
                        @foreach($availableBoms as $bom)
                            <option value="{{ $bom['id'] }}">{{ $bom['name'] }}</option>
                        @endforeach
                    </select>
                    @error('bom_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @if($product_id && empty($availableBoms))
                        <small class="text-warning">No active BOMs for this product. Create and activate a BOM first.</small>
                    @endif
                </div>

                <div class="col-md-4">
                    <label class="form-label">Warehouse <span class="text-danger">*</span></label>
                    <select wire:model="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror">
                        <option value="">Select warehouse...</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Planned Quantity <span class="text-danger">*</span></label>
                    <input type="number" wire:model="planned_quantity" class="form-control @error('planned_quantity') is-invalid @enderror" step="0.01" min="0.01">
                    @error('planned_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Priority</label>
                    <select wire:model="priority" class="form-select">
                        <option value="low">Low</option>
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Planned Start Date</label>
                    <input type="date" wire:model="planned_start_date" class="form-control">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Planned End Date</label>
                    <input type="date" wire:model="planned_end_date" class="form-control">
                </div>

                <div class="col-md-4"></div>

                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea wire:model="notes" class="form-control" rows="3" placeholder="Optional notes..."></textarea>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('manufacturing.orders.index') }}" class="btn btn-light" wire:navigate>Cancel</a>
            <button type="button" class="btn btn-primary btn-wave" wire:click="save" wire:loading.attr="disabled">
                <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                Create Order
            </button>
        </div>
    </div>
</div>
