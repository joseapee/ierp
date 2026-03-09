<div>
    @if($show)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">{{ $bomId ? 'Edit BOM' : 'Create BOM' }}</h6>
                    <button type="button" class="btn-close" wire:click="$set('show', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Product <span class="text-danger">*</span></label>
                            <select wire:model="product_id" class="form-select @error('product_id') is-invalid @enderror" {{ $bomId ? 'disabled' : '' }}>
                                <option value="">Select product...</option>
                                @foreach($manufacturedProducts as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                @endforeach
                            </select>
                            @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">BOM Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Standard Recipe">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Version <span class="text-danger">*</span></label>
                            <input type="text" wire:model="version" class="form-control @error('version') is-invalid @enderror">
                            @error('version') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Yield Quantity <span class="text-danger">*</span></label>
                            <input type="number" wire:model="yield_quantity" class="form-control @error('yield_quantity') is-invalid @enderror" step="0.01" min="0.01">
                            @error('yield_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Description</label>
                            <textarea wire:model="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Notes</label>
                            <textarea wire:model="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>

                    {{-- Materials Table --}}
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Materials</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-wave" wire:click="addItem">
                            <i class="ri-add-line me-1"></i> Add Material
                        </button>
                    </div>
                    @error('items') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:35%">Material (Product)</th>
                                    <th style="width:15%">Quantity</th>
                                    <th style="width:15%">Unit Cost</th>
                                    <th style="width:12%">Wastage %</th>
                                    <th style="width:15%" class="text-end">Line Total</th>
                                    <th style="width:8%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $index => $item)
                                <tr wire:key="bom-item-{{ $index }}">
                                    <td>
                                        <select wire:model="items.{{ $index }}.product_id" class="form-select form-select-sm @error('items.'.$index.'.product_id') is-invalid @enderror">
                                            <option value="">Select material...</option>
                                            @foreach($rawMaterials as $material)
                                                <option value="{{ $material->id }}">{{ $material->name }} ({{ $material->sku }})</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" wire:model.live="items.{{ $index }}.quantity" class="form-control form-control-sm @error('items.'.$index.'.quantity') is-invalid @enderror" step="0.01" min="0.01">
                                    </td>
                                    <td>
                                        <input type="number" wire:model.live="items.{{ $index }}.unit_cost" class="form-control form-control-sm @error('items.'.$index.'.unit_cost') is-invalid @enderror" step="0.01" min="0">
                                    </td>
                                    <td>
                                        <input type="number" wire:model.live="items.{{ $index }}.wastage_percentage" class="form-control form-control-sm" step="0.1" min="0" max="100">
                                    </td>
                                    <td class="text-end align-middle">
                                        @php
                                            $qty = (float)($item['quantity'] ?? 0);
                                            $cost = (float)($item['unit_cost'] ?? 0);
                                            $wastage = (float)($item['wastage_percentage'] ?? 0);
                                            $lineTotal = $qty * $cost * (1 + $wastage / 100);
                                        @endphp
                                        {{ number_format($lineTotal, 2) }}
                                    </td>
                                    <td class="text-center">
                                        @if(count($items) > 1)
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-wave" wire:click="removeItem({{ $index }})">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="4" class="text-end fw-medium">Total Cost (per yield):</td>
                                    <td class="text-end fw-bold">
                                        @php
                                            $total = collect($items)->sum(function($item) {
                                                $qty = (float)($item['quantity'] ?? 0);
                                                $cost = (float)($item['unit_cost'] ?? 0);
                                                $wastage = (float)($item['wastage_percentage'] ?? 0);
                                                return $qty * $cost * (1 + $wastage / 100);
                                            });
                                            $yieldQty = (float)$yield_quantity ?: 1;
                                        @endphp
                                        {{ number_format($total / $yieldQty, 2) }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="$set('show', false)">Cancel</button>
                    <button type="button" class="btn btn-primary btn-wave" wire:click="save" wire:loading.attr="disabled">
                        <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                        {{ $bomId ? 'Update BOM' : 'Create BOM' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
