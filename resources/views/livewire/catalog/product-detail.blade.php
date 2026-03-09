<div>
    @section('title', $product->name)

    <x-page-header :title="$product->name" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Products', 'route' => 'products.index'],
        ['label' => $product->name],
    ]">
        <x-slot:actions>
            @can('products.edit')
                <button class="btn btn-primary btn-wave"
                        wire:click="$dispatch('openProductFormModal', { productId: {{ $product->id }} })">
                    <i class="ri-edit-line me-1"></i> Edit
                </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="row">
        {{-- Product Info Card --}}
        <div class="col-xl-4">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Product Information</div>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">SKU</span>
                            <code>{{ $product->sku }}</code>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Type</span>
                            <span class="badge bg-info-transparent">{{ ucfirst($product->type) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Category</span>
                            <span>{{ $product->category?->name ?? '—' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Brand</span>
                            <span>{{ $product->brand?->name ?? '—' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Base Unit</span>
                            <span>{{ $product->baseUnit?->name }} ({{ $product->baseUnit?->abbreviation }})</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Barcode</span>
                            <span>{{ $product->barcode ?? '—' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Status</span>
                            @if($product->is_active)
                                <span class="badge bg-success-transparent">Active</span>
                            @else
                                <span class="badge bg-danger-transparent">Inactive</span>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Pricing Card --}}
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Pricing & Valuation</div>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Cost Price</span>
                            <span>{{ number_format((float) $product->cost_price, 4) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Sell Price</span>
                            <span>{{ number_format((float) $product->sell_price, 4) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Tax Rate</span>
                            <span>{{ $product->tax_rate }}%</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Valuation</span>
                            <span class="badge bg-secondary-transparent">{{ strtoupper(str_replace('_', ' ', $product->valuation_method)) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Reorder Level</span>
                            <span>{{ $product->reorder_level }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Reorder Qty</span>
                            <span>{{ $product->reorder_quantity }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Variants & Stock --}}
        <div class="col-xl-8">
            @if($product->description)
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Description</div>
                </div>
                <div class="card-body">
                    <p>{{ $product->description }}</p>
                </div>
            </div>
            @endif

            {{-- Variants table for variable products --}}
            @if($product->type === 'variable')
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title">Variants ({{ $product->variants->count() }})</div>
                    @can('products.edit')
                        <button class="btn btn-sm btn-primary btn-wave"
                                wire:click="$dispatch('openVariantManager', { productId: {{ $product->id }} })">
                            <i class="ri-settings-3-line me-1"></i> Manage Variants
                        </button>
                    @endcan
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th>SKU</th>
                                    <th>Attributes</th>
                                    <th class="text-end">Cost</th>
                                    <th class="text-end">Price</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($product->variants as $variant)
                                <tr>
                                    <td><code>{{ $variant->sku }}</code></td>
                                    <td>
                                        @foreach($variant->attributeValues as $av)
                                            <span class="badge bg-outline-secondary">{{ $av->attribute->name }}: {{ $av->value }}</span>
                                        @endforeach
                                    </td>
                                    <td class="text-end">{{ number_format((float) ($variant->cost_price_override ?? $product->cost_price), 2) }}</td>
                                    <td class="text-end">{{ number_format((float) ($variant->sell_price_override ?? $product->sell_price), 2) }}</td>
                                    <td>
                                        @if($variant->is_active)
                                            <span class="badge bg-success-transparent">Active</span>
                                        @else
                                            <span class="badge bg-danger-transparent">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No variants generated yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- Stock Batches --}}
            @if($product->is_stockable)
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Stock Batches</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th>Batch #</th>
                                    <th>Warehouse</th>
                                    <th class="text-end">Remaining</th>
                                    <th>Expiry</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($product->stockBatches as $batch)
                                <tr>
                                    <td><code>{{ $batch->batch_number ?? '—' }}</code></td>
                                    <td>{{ $batch->warehouse?->name }}</td>
                                    <td class="text-end">{{ number_format((float) $batch->remaining_quantity, 2) }}</td>
                                    <td>{{ $batch->expiry_date?->format('Y-m-d') ?? '—' }}</td>
                                    <td><span class="badge bg-{{ $batch->status === 'available' ? 'success' : 'warning' }}-transparent">{{ ucfirst($batch->status) }}</span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No stock batches.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <livewire:catalog.product-form-modal />
</div>
