<div>
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color:rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">{{ $productId ? 'Edit Product' : 'Add Product' }}</h6>
                    <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                </div>
                <form wire:submit="save">
                    <div class="modal-body">
                        <div class="row gy-3">
                            {{-- Basic Info --}}
                            <div class="col-md-6">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model.live.debounce.300ms="name" class="form-control @error('name') is-invalid @enderror">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Slug <span class="text-danger">*</span></label>
                                <input type="text" wire:model="slug" class="form-control @error('slug') is-invalid @enderror">
                                @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">SKU <span class="text-danger">*</span></label>
                                <input type="text" wire:model="sku" class="form-control @error('sku') is-invalid @enderror">
                                @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Type & Classification --}}
                            <div class="col-md-3">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select wire:model="type" class="form-select @error('type') is-invalid @enderror">
                                    <option value="standard">Standard</option>
                                    <option value="variable">Variable</option>
                                    <option value="service">Service</option>
                                    <option value="bundle">Bundle</option>
                                </select>
                                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Category</label>
                                <select wire:model="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                    <option value="">— None —</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Brand</label>
                                <select wire:model="brand_id" class="form-select @error('brand_id') is-invalid @enderror">
                                    <option value="">— None —</option>
                                    @foreach($brands as $br)
                                        <option value="{{ $br->id }}">{{ $br->name }}</option>
                                    @endforeach
                                </select>
                                @error('brand_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Base Unit <span class="text-danger">*</span></label>
                                <select wire:model="base_unit_id" class="form-select @error('base_unit_id') is-invalid @enderror">
                                    <option value="">— Select —</option>
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->abbreviation }})</option>
                                    @endforeach
                                </select>
                                @error('base_unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Pricing --}}
                            <div class="col-md-3">
                                <label class="form-label">Cost Price</label>
                                <input type="number" step="0.0001" wire:model="cost_price" class="form-control @error('cost_price') is-invalid @enderror">
                                @error('cost_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sell Price</label>
                                <input type="number" step="0.0001" wire:model="sell_price" class="form-control @error('sell_price') is-invalid @enderror">
                                @error('sell_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tax Rate (%)</label>
                                <input type="number" step="0.01" wire:model="tax_rate" class="form-control @error('tax_rate') is-invalid @enderror">
                                @error('tax_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Valuation <span class="text-danger">*</span></label>
                                <select wire:model="valuation_method" class="form-select @error('valuation_method') is-invalid @enderror">
                                    <option value="fifo">FIFO</option>
                                    <option value="lifo">LIFO</option>
                                    <option value="weighted_average">Weighted Average</option>
                                    <option value="standard">Standard Cost</option>
                                </select>
                                @error('valuation_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Barcode & Reorder --}}
                            <div class="col-md-4">
                                <label class="form-label">Barcode</label>
                                <input type="text" wire:model="barcode" class="form-control @error('barcode') is-invalid @enderror">
                                @error('barcode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Reorder Level</label>
                                <input type="number" step="0.01" wire:model="reorder_level" class="form-control @error('reorder_level') is-invalid @enderror">
                                @error('reorder_level') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Reorder Quantity</label>
                                <input type="number" step="0.01" wire:model="reorder_quantity" class="form-control @error('reorder_quantity') is-invalid @enderror">
                                @error('reorder_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Descriptions --}}
                            <div class="col-md-6">
                                <label class="form-label">Short Description</label>
                                <textarea wire:model="short_description" class="form-control @error('short_description') is-invalid @enderror" rows="2"></textarea>
                                @error('short_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Description</label>
                                <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="2"></textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Attributes (for variable products) --}}
                            @if($type === 'variable')
                            <div class="col-12">
                                <label class="form-label">Product Attributes</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($attributes as $attr)
                                    <div class="form-check">
                                        <input type="checkbox" wire:model="attribute_ids" value="{{ $attr->id }}" class="form-check-input" id="attr-{{ $attr->id }}">
                                        <label class="form-check-label" for="attr-{{ $attr->id }}">{{ $attr->name }}</label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            {{-- Toggles --}}
                            <div class="col-12">
                                <div class="d-flex flex-wrap gap-4">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" wire:model="is_active" class="form-check-input" id="prodActive">
                                        <label class="form-check-label" for="prodActive">Active</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" wire:model="is_purchasable" class="form-check-input" id="prodPurchasable">
                                        <label class="form-check-label" for="prodPurchasable">Purchasable</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" wire:model="is_sellable" class="form-check-input" id="prodSellable">
                                        <label class="form-check-label" for="prodSellable">Sellable</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" wire:model="is_stockable" class="form-check-input" id="prodStockable">
                                        <label class="form-check-label" for="prodStockable">Stockable</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="$set('showModal', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-wave">
                            <span wire:loading.remove wire:target="save">{{ $productId ? 'Update' : 'Create' }}</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
