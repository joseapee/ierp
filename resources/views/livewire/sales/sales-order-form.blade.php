<div>
    @section('title', 'New Sales Order')

    <x-page-header title="New Sales Order" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Sales Orders', 'route' => 'sales.orders.index'],
        ['label' => 'New'],
    ]" />

    <div class="card custom-card">
        <div class="card-header"><div class="card-title">Order Details</div></div>
        <div class="card-body">
            <div class="row gy-3">
                <div class="col-md-4">
                    <label class="form-label">Customer <span class="text-danger">*</span></label>
                    <select wire:model="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                        <option value="">Select Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                    @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Order Date <span class="text-danger">*</span></label>
                    <input type="date" wire:model="order_date" class="form-control @error('order_date') is-invalid @enderror">
                    @error('order_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Due Date</label>
                    <input type="date" wire:model="due_date" class="form-control">
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
                            <th style="width:25%">Product</th>
                            <th style="width:13%">Warehouse</th>
                            <th style="width:10%">Qty</th>
                            <th style="width:10%">Unit Price</th>
                            <th style="width:8%">Disc %</th>
                            <th style="width:8%">Tax %</th>
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
                                <input type="number" step="0.0001" wire:model.live="items.{{ $i }}.quantity" class="form-control form-control-sm">
                            </td>
                            <td>
                                <input type="number" step="0.01" wire:model.live="items.{{ $i }}.unit_price" class="form-control form-control-sm">
                            </td>
                            <td>
                                <input type="number" step="0.01" wire:model.live="items.{{ $i }}.discount_percent" class="form-control form-control-sm">
                            </td>
                            <td>
                                <input type="number" step="0.01" wire:model.live="items.{{ $i }}.tax_rate" class="form-control form-control-sm">
                            </td>
                            <td class="text-end align-middle">
                                @php
                                    $lineGross = (float)($item['quantity'] ?? 0) * (float)($item['unit_price'] ?? 0);
                                    $lineDisc = $lineGross * (float)($item['discount_percent'] ?? 0) / 100;
                                    $lineSub = $lineGross - $lineDisc;
                                    $lineTax = $lineSub * (float)($item['tax_rate'] ?? 0) / 100;
                                    $lineTotal = $lineSub + $lineTax;
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
                            <td colspan="6" class="text-end fw-medium">Subtotal:</td>
                            <td class="text-end">{{ format_currency($this->subtotal) }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="6" class="text-end fw-medium">Tax:</td>
                            <td class="text-end">{{ format_currency($this->taxTotal) }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="6" class="text-end fw-bold">Grand Total:</td>
                            <td class="text-end fw-bold">{{ format_currency($this->grandTotal) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('sales.orders.index') }}" class="btn btn-light" wire:navigate>Cancel</a>
            <button type="button" class="btn btn-primary btn-wave" wire:click="save" wire:loading.attr="disabled">
                <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                Create Sales Order
            </button>
        </div>
    </div>
</div>
