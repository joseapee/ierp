<div>
    @section('title', $productId ? 'Edit Product' : 'New Product')

    <x-page-header :title="$productId ? 'Edit Product' : 'New Product'" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Products', 'route' => 'products.index'],
        ['label' => $productId ? 'Edit' : 'New'],
    ]" />

    {{-- Step Indicator --}}
    <div class="card custom-card mb-3">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center">
                @foreach($steps as $num => $label)
                    <div wire:key="step-indicator-{{ $num }}"
                         class="d-flex align-items-center gap-2 {{ $num < $currentStep ? 'text-success' : ($num === $currentStep ? 'text-primary fw-bold' : 'text-muted') }}"
                         style="cursor: {{ $num < $currentStep ? 'pointer' : 'default' }}"
                         @if($num < $currentStep) wire:click="goToStep({{ $num }})" @endif>
                        <span class="avatar avatar-xs avatar-rounded {{ $num < $currentStep ? 'bg-success' : ($num === $currentStep ? 'bg-primary' : 'bg-light') }}">
                            @if($num < $currentStep)
                                <i class="ri-check-line text-white"></i>
                            @else
                                <span class="{{ $num === $currentStep ? 'text-white' : '' }}">{{ $num }}</span>
                            @endif
                        </span>
                        <span class="d-none d-md-inline">{{ $label }}</span>
                    </div>
                    @if(!$loop->last)
                        <div class="flex-grow-1 mx-2" style="height:2px;background:{{ $num < $currentStep ? '#198754' : '#dee2e6' }}"></div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- Step 1: Type Selection --}}
    @if($currentStep === 1)
    <div wire:key="step-1" class="card custom-card">
        <div class="card-header"><div class="card-title">Select Product Type</div></div>
        <div class="card-body">
            @error('type') <div class="alert alert-danger mb-3">{{ $message }}</div> @enderror
            <div class="row g-3">
                @php
                    $types = [
                        'standard' => ['icon' => 'ri-shopping-bag-line', 'label' => 'Purchased Product', 'desc' => 'Product purchased from suppliers'],
                        'manufactured' => ['icon' => 'ri-tools-line', 'label' => 'Manufactured Product', 'desc' => 'Product built from raw materials via BOM'],
                        'variable' => ['icon' => 'ri-palette-line', 'label' => 'Variable Product', 'desc' => 'Product with variants (size, color, etc.)'],
                        'service' => ['icon' => 'ri-service-line', 'label' => 'Service', 'desc' => 'Non-stockable service offering'],
                        'bundle' => ['icon' => 'ri-stack-line', 'label' => 'Bundle / Kit', 'desc' => 'Pre-packaged group of products'],
                    ];
                @endphp
                @foreach($types as $value => $info)
                <div class="col-md-4 col-lg" wire:key="type-card-{{ $value }}">
                    <div class="card border {{ $type === $value ? 'border-primary shadow-sm' : '' }}"
                         style="cursor:pointer" wire:click="selectType('{{ $value }}')">
                        <div class="card-body text-center py-4">
                            <i class="{{ $info['icon'] }} fs-1 {{ $type === $value ? 'text-primary' : 'text-muted' }}"></i>
                            <h6 class="mt-2 mb-1">{{ $info['label'] }}</h6>
                            <small class="text-muted">{{ $info['desc'] }}</small>
                            @if($type === $value)
                                <div class="mt-2"><span class="badge bg-primary">Selected</span></div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Step 2: Details --}}
    @if($currentStep === 2)
    <div wire:key="step-2" class="card custom-card">
        <div class="card-header"><div class="card-title">Product Details</div></div>
        <div class="card-body">
            <div class="row gy-3">
                <div class="col-md-6">
                    <label class="form-label">Name <span class="text-danger">*</span> <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="The display name of the product shown across all modules"></i></label>
                    <input type="text" wire:model.live.debounce.300ms="name" class="form-control @error('name') is-invalid @enderror">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                {{-- <div class="col-md-3">
                    <label class="form-label">Slug <span class="text-danger">*</span> <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="URL-friendly identifier, auto-generated from the product name"></i></label>
                    <input type="text" wire:model="slug" readonly class="form-control @error('slug') is-invalid @enderror">
                    @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div> --}}
                <div class="col-md-3">
                    <label class="form-label">SKU <span class="text-danger">*</span> <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Stock Keeping Unit — a unique code to identify this product in inventory"></i></label>
                    <input type="text" wire:model="sku" class="form-control @error('sku') is-invalid @enderror">
                    @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Category <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Group products under a category for reporting and filtering"></i></label>
                    <select wire:model="category_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Brand <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="The manufacturer or brand associated with this product"></i></label>
                    <select wire:model="brand_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Base Unit <span class="text-danger">*</span> <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Primary unit of measure for inventory tracking (e.g. pcs, kg, ltr)"></i></label>
                    <select wire:model="base_unit_id" class="form-select @error('base_unit_id') is-invalid @enderror">
                        <option value="">— Select —</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->abbreviation }})</option>
                        @endforeach
                    </select>
                    @error('base_unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Short Description <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="A brief summary shown in product listings and cards"></i></label>
                    <input type="text" wire:model="short_description" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Description <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Detailed product information for internal reference"></i></label>
                    <textarea wire:model="description" class="form-control" rows="2"></textarea>
                </div>
            </div>

            @if($type === 'variable')
            <hr>
            <h6>Product Attributes <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Select attributes (e.g. Size, Color) to generate product variants"></i></h6>
            <div class="row">
                @foreach($attributes as $attr)
                <div class="col-md-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" value="{{ $attr->id }}" wire:model="attribute_ids" id="attr-{{ $attr->id }}">
                        <label class="form-check-label" for="attr-{{ $attr->id }}">{{ $attr->name }}</label>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Step 3: Inventory --}}
    @if($currentStep === 3)
    <div wire:key="step-3" class="card custom-card">
        <div class="card-header"><div class="card-title">Inventory Settings</div></div>
        <div class="card-body">
            <div class="row gy-3">
                <div class="col-md-4">
                    <label class="form-label">Barcode <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Scannable barcode (EAN, UPC, etc.) for warehouse operations"></i></label>
                    <input type="text" wire:model="barcode" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Valuation Method <span class="text-danger">*</span> <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="How inventory cost is calculated when stock is consumed or sold"></i></label>
                    <select wire:model="valuation_method" class="form-select @error('valuation_method') is-invalid @enderror">
                        <option value="weighted_average">Weighted Average</option>
                        <option value="fifo">FIFO</option>
                        <option value="lifo">LIFO</option>
                        <option value="standard">Standard Cost</option>
                    </select>
                    @error('valuation_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label">Reorder Level <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Minimum stock quantity before a reorder alert is triggered"></i></label>
                    <input type="number" step="0.01" wire:model="reorder_level" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Reorder Qty <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Default quantity to reorder when stock falls below the reorder level"></i></label>
                    <input type="number" step="0.01" wire:model="reorder_quantity" class="form-control">
                </div>
                <div class="col-12">
                    <div class="d-flex gap-4 flex-wrap">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" wire:model="is_active" id="sw-active">
                            <label class="form-check-label" for="sw-active">Active <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Inactive products are hidden from transactions but kept for historical records"></i></label>
                        </div>
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" wire:model="is_purchasable" id="sw-purchasable">
                            <label class="form-check-label" for="sw-purchasable">Purchasable <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Whether this product can appear on purchase orders"></i></label>
                        </div>
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" wire:model="is_sellable" id="sw-sellable">
                            <label class="form-check-label" for="sw-sellable">Sellable <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Whether this product can appear on sales orders and invoices"></i></label>
                        </div>
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" wire:model="is_stockable" id="sw-stockable">
                            <label class="form-check-label" for="sw-stockable">Stockable <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Whether this product is tracked in warehouse inventory"></i></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Step 4: Manufacturing (only for manufactured products) --}}
    @if($currentStep === 4 && $type === 'manufactured')
    <div wire:key="step-4" class="card custom-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="card-title">Bill of Materials</div>
            <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addBomItem">
                <i class="ri-add-line me-1"></i> Add Material
            </button>
        </div>
        <div class="card-body">
            <div class="row gy-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">BOM Name <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="A descriptive name for this bill of materials"></i></label>
                    <input type="text" wire:model="bomName" class="form-control" placeholder="{{ $name }} BOM">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Version <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Version identifier for tracking BOM revisions (e.g. 1.0, 2.0)"></i></label>
                    <input type="text" wire:model="bomVersion" class="form-control">
                </div>
            </div>
            @error('bomItems') <div class="alert alert-danger">{{ $message }}</div> @enderror
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:35%">Raw Material <i class="ri-information-line text-muted fw-normal" data-bs-toggle="tooltip" title="The purchased material or component used in production"></i></th>
                            <th>Quantity <i class="ri-information-line text-muted fw-normal" data-bs-toggle="tooltip" title="Amount of this material needed to produce one finished unit"></i></th>
                            <th>Unit Cost <i class="ri-information-line text-muted fw-normal" data-bs-toggle="tooltip" title="Cost per unit of material — auto-filled from inventory when a material is selected"></i></th>
                            <th>Wastage % <i class="ri-information-line text-muted fw-normal" data-bs-toggle="tooltip" title="Expected percentage of material lost during the production process"></i></th>
                            <th class="text-end">Line Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bomItems as $i => $item)
                        <tr wire:key="bom-item-{{ $i }}">
                            <td>
                                <select wire:model.live="bomItems.{{ $i }}.product_id" class="form-select form-select-sm @error('bomItems.'.$i.'.product_id') is-invalid @enderror">
                                    <option value="">— Select Material —</option>
                                    @foreach($rawMaterials as $mat)
                                        <option value="{{ $mat->id }}">{{ $mat->name }} ({{ $mat->sku }})</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" step="0.0001" wire:model.live="bomItems.{{ $i }}.quantity" class="form-control form-control-sm @error('bomItems.'.$i.'.quantity') is-invalid @enderror">
                            </td>
                            <td>
                                <input type="number" step="0.0001" wire:model.live="bomItems.{{ $i }}.unit_cost" class="form-control form-control-sm @error('bomItems.'.$i.'.unit_cost') is-invalid @enderror">
                            </td>
                            <td>
                                <input type="number" step="0.01" wire:model.live="bomItems.{{ $i }}.wastage_percentage" class="form-control form-control-sm">
                            </td>
                            <td class="text-end">
                                @php
                                    $qty = (float)($item['quantity'] ?? 0);
                                    $cost = (float)($item['unit_cost'] ?? 0);
                                    $waste = (float)($item['wastage_percentage'] ?? 0);
                                    $lineTotal = $qty * $cost * (1 + $waste/100);
                                @endphp
                                {{ format_currency($lineTotal) }}
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeBomItem({{ $i }})">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">No materials added. Click "Add Material" to start building the BOM.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if(count($bomItems) > 0)
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="4" class="text-end">Total Material Cost:</td>
                            <td class="text-end">{{ format_currency((float)$cost_price) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Step 5: Pricing --}}
    @if($currentStep === 5)
    <div wire:key="step-5" class="card custom-card">
        <div class="card-header"><div class="card-title">Pricing</div></div>
        <div class="card-body">
            <div class="row gy-3">
                <div class="col-md-4">
                    <label class="form-label">Cost Price <span class="text-danger">*</span> <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Base cost of the product. For manufactured items, this is auto-calculated from the BOM"></i></label>
                    <input type="number" step="0.0001" wire:model="cost_price" class="form-control @error('cost_price') is-invalid @enderror" @if($type === 'manufactured') readonly @endif>
                    @error('cost_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @if($type === 'manufactured')
                        <small class="text-muted">Calculated from BOM</small>
                    @endif
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pricing Mode <span class="text-danger">*</span> <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="How the sell price is determined: set manually, by percentage markup, or by a fixed profit margin"></i></label>
                    <select wire:model.live="pricing_mode" class="form-select @error('pricing_mode') is-invalid @enderror">
                        <option value="manual">Manual Price</option>
                        <option value="percentage_markup">Percentage Markup</option>
                        <option value="fixed_profit">Fixed Profit</option>
                    </select>
                    @error('pricing_mode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                @if($pricing_mode === 'percentage_markup')
                <div class="col-md-4">
                    <label class="form-label">Markup % <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Percentage added on top of cost price to calculate the sell price"></i></label>
                    <input type="number" step="0.01" wire:model.live="markup_percentage" class="form-control">
                </div>
                @elseif($pricing_mode === 'fixed_profit')
                <div class="col-md-4">
                    <label class="form-label">Profit Amount <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Fixed monetary amount added to cost price to determine the sell price"></i></label>
                    <input type="number" step="0.01" wire:model.live="profit_amount" class="form-control">
                </div>
                @endif
                <div class="col-md-4">
                    <label class="form-label">Sell Price <span class="text-danger">*</span> <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Price at which the product is sold to customers"></i></label>
                    <input type="number" step="0.0001" wire:model="sell_price" class="form-control @error('sell_price') is-invalid @enderror" @if($pricing_mode !== 'manual') readonly @endif>
                    @error('sell_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @if($pricing_mode !== 'manual')
                        <small class="text-muted">Auto-calculated from {{ $pricing_mode === 'percentage_markup' ? 'markup' : 'profit' }}</small>
                    @endif
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tax Rate (%) <i class="ri-information-line text-muted ms-1" data-bs-toggle="tooltip" title="Tax percentage applied to this product on invoices and sales orders"></i></label>
                    <input type="number" step="0.01" wire:model="tax_rate" class="form-control">
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Step 6: Review --}}
    @if($currentStep === 6)
    <div wire:key="step-6" class="card custom-card">
        <div class="card-header"><div class="card-title">Review & Confirm</div></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card border">
                        <div class="card-body">
                            <h6 class="text-muted mb-3">Product Details</h6>
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Type</dt><dd class="col-sm-8"><span class="badge bg-info-transparent">{{ ucfirst($type) }}</span></dd>
                                <dt class="col-sm-4">Name</dt><dd class="col-sm-8">{{ $name }}</dd>
                                <dt class="col-sm-4">SKU</dt><dd class="col-sm-8"><code>{{ $sku }}</code></dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border">
                        <div class="card-body">
                            <h6 class="text-muted mb-3">Pricing</h6>
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Cost</dt><dd class="col-sm-8">{{ format_currency((float)$cost_price) }}</dd>
                                <dt class="col-sm-4">Sell Price</dt><dd class="col-sm-8">{{ format_currency((float)$sell_price) }}</dd>
                                <dt class="col-sm-4">Mode</dt><dd class="col-sm-8">{{ ucfirst(str_replace('_', ' ', $pricing_mode)) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                @if($type === 'manufactured' && count($bomItems) > 0)
                <div class="col-12">
                    <div class="card border">
                        <div class="card-body">
                            <h6 class="text-muted mb-3">Bill of Materials ({{ count($bomItems) }} items)</h6>
                            <table class="table table-sm mb-0">
                                <thead><tr><th>Material</th><th>Qty</th><th>Cost</th><th class="text-end">Total</th></tr></thead>
                                <tbody>
                                @foreach($bomItems as $item)
                                    @php
                                        $mat = $rawMaterials->firstWhere('id', $item['product_id']);
                                        $q = (float)($item['quantity'] ?? 0);
                                        $c = (float)($item['unit_cost'] ?? 0);
                                    @endphp
                                    <tr>
                                        <td>{{ $mat?->name ?? 'Unknown' }}</td>
                                        <td>{{ $q }}</td>
                                        <td>{{ format_currency($c) }}</td>
                                        <td class="text-end">{{ format_currency($q * $c) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
                <div class="col-12">
                    <div class="card border">
                        <div class="card-body">
                            <h6 class="text-muted mb-3">Inventory Flags</h6>
                            <div class="d-flex gap-3 flex-wrap">
                                <span class="badge {{ $is_active ? 'bg-success-transparent' : 'bg-secondary-transparent' }}">{{ $is_active ? 'Active' : 'Inactive' }}</span>
                                <span class="badge {{ $is_purchasable ? 'bg-primary-transparent' : 'bg-secondary-transparent' }}">{{ $is_purchasable ? 'Purchasable' : 'Not Purchasable' }}</span>
                                <span class="badge {{ $is_sellable ? 'bg-primary-transparent' : 'bg-secondary-transparent' }}">{{ $is_sellable ? 'Sellable' : 'Not Sellable' }}</span>
                                <span class="badge {{ $is_stockable ? 'bg-primary-transparent' : 'bg-secondary-transparent' }}">{{ $is_stockable ? 'Stockable' : 'Not Stockable' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Navigation Buttons --}}
    <div class="d-flex justify-content-between mb-4">
        <div>
            @if($currentStep > 1)
                <button type="button" class="btn btn-light" wire:click="previousStep">
                    <i class="ri-arrow-left-line me-1"></i> Previous
                </button>
            @else
                <a href="{{ route('products.index') }}" class="btn btn-light" wire:navigate>Cancel</a>
            @endif
        </div>
        <div>
            @if($currentStep < $totalSteps)
                <button type="button" class="btn btn-primary" wire:click="nextStep">
                    Next <i class="ri-arrow-right-line ms-1"></i>
                </button>
            @else
                <button type="button" class="btn btn-success" wire:click="save">
                    <span wire:loading.remove wire:target="save">
                        <i class="ri-check-line me-1"></i> {{ $productId ? 'Update Product' : 'Create Product' }}
                    </span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            @endif
        </div>
    </div>
</div>
