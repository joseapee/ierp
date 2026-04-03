<div>
    @section('title', 'New Purchase Order')

    <x-page-header title="New Purchase Order" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Purchase Orders', 'route' => 'procurement.purchase-orders.index'],
        ['label' => 'New'],
    ]" />

    <div class="card custom-card">
        <div class="card-header"><div class="card-title">Order Details</div></div>
        <div class="card-body">
            <div class="row gy-3">
                <div class="col-md-4">
                    <label class="form-label">Supplier <span class="text-danger">*</span></label>
                    <select wire:model="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
                        <option value="">Select Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Order Date <span class="text-danger">*</span></label>
                    <input type="date" wire:model="order_date" class="form-control @error('order_date') is-invalid @enderror">
                    @error('order_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Expected Date</label>
                    <input type="date" wire:model="expected_date" class="form-control">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Notes</label>
                    <textarea wire:model="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="card custom-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="card-title">Line Items</div>
            <button type="button" class="btn btn-sm btn-outline-primary btn-wave" wire:click="addItem">
                <i class="ri-add-line me-1"></i> Add Item
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:30%">Product</th>
                            <th style="width:15%">Warehouse</th>
                            <th style="width:12%">Qty</th>
                            <th style="width:12%">Unit Price</th>
                            <th style="width:10%">Tax %</th>
                            <th class="text-end" style="width:14%">Line Total</th>
                            <th style="width:7%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $i => $item)
                        <tr wire:key="item-{{ $i }}">
                            <td>
                                <select wire:model="items.{{ $i }}.product_id" class="form-select form-select-sm @error('items.'.$i.'.product_id') is-invalid @enderror">
                                    <option value="">Select Product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select wire:model="items.{{ $i }}.warehouse_id" class="form-select form-select-sm @error('items.'.$i.'.warehouse_id') is-invalid @enderror">
                                    <option value="">Select</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" step="0.0001" wire:model.live="items.{{ $i }}.quantity" class="form-control form-control-sm @error('items.'.$i.'.quantity') is-invalid @enderror">
                            </td>
                            <td>
                                <input type="number" step="0.01" wire:model.live="items.{{ $i }}.unit_price" class="form-control form-control-sm @error('items.'.$i.'.unit_price') is-invalid @enderror">
                            </td>
                            <td>
                                <input type="number" step="0.01" wire:model.live="items.{{ $i }}.tax_rate" class="form-control form-control-sm">
                            </td>
                            <td class="text-end align-middle">
                                @php
                                    $lineQty = (float)($item['quantity'] ?? 0);
                                    $linePrice = (float)($item['unit_price'] ?? 0);
                                    $lineTax = (float)($item['tax_rate'] ?? 0);
                                    $lineSub = $lineQty * $linePrice;
                                    $lineTotal = $lineSub + ($lineSub * $lineTax / 100);
                                @endphp
                                {{ format_currency($lineTotal) }}
                            </td>
                            <td class="text-center align-middle">
                                @if(count($items) > 1)
                                <button type="button" class="btn btn-sm btn-outline-danger btn-wave" wire:click="removeItem({{ $i }})">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-end fw-medium">Subtotal:</td>
                            <td class="text-end">{{ format_currency($this->subtotal) }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-end fw-medium">Tax:</td>
                            <td class="text-end">{{ format_currency($this->taxTotal) }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-end fw-bold">Grand Total:</td>
                            <td class="text-end fw-bold">{{ format_currency($this->grandTotal) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('procurement.purchase-orders.index') }}" class="btn btn-light" wire:navigate>Cancel</a>
            <button type="button" class="btn btn-primary btn-wave" wire:click="save" wire:loading.attr="disabled">
                <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                Create Purchase Order
            </button>
        </div>
    </div>
</div>
