<div>
    @section('title', 'Products')

    <x-page-header title="Products" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Products'],
    ]">
        <x-slot:actions>
            @can('products.create')
                <a href="{{ route('products.create') }}" class="btn btn-primary btn-wave" wire:navigate>
                    <i class="ri-add-line me-1"></i> Add Product
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card custom-card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex gap-2 flex-wrap">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" style="width:220px" placeholder="Search products...">
                <select wire:model.live="typeFilter" class="form-select form-select-sm" style="width:160px">
                    <option value="">All Types</option>
                    <option value="standard">Standard</option>
                    <option value="manufactured">Manufactured</option>
                    <option value="variable">Variable</option>
                    <option value="service">Service</option>
                    <option value="bundle">Bundle</option>
                </select>
                <select wire:model.live="statusFilter" class="form-select form-select-sm" style="width:140px">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th class="text-end">Cost</th>
                            <th class="text-end">Price</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr wire:key="product-{{ $product->id }}">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar avatar-sm avatar-rounded bg-primary-transparent">
                                        {{ strtoupper(substr($product->name, 0, 1)) }}
                                    </span>
                                    <div>
                                        <span class="d-block fw-medium">{{ $product->name }}</span>
                                        @if($product->brand)
                                            <small class="text-muted">{{ $product->brand->name }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td><code>{{ $product->sku }}</code></td>
                            <td><span class="badge bg-info-transparent">{{ ucfirst($product->type) }}</span></td>
                            <td>{{ $product->category?->name ?? '—' }}</td>
                            <td class="text-end">{{ number_format((float) $product->cost_price, 2) }}</td>
                            <td class="text-end">{{ number_format((float) $product->sell_price, 2) }}</td>
                            <td>
                                @if($product->is_active)
                                    <span class="badge bg-success-transparent">Active</span>
                                @else
                                    <span class="badge bg-danger-transparent">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('products.show', $product) }}"
                                       class="btn btn-sm btn-outline-primary btn-wave" wire:navigate>
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    @can('products.edit')
                                    <a href="{{ route('products.edit', $product) }}"
                                       class="btn btn-sm btn-outline-info btn-wave" wire:navigate>
                                        <i class="ri-edit-line"></i>
                                    </a>
                                    @endcan
                                    @can('products.delete')
                                    <button class="btn btn-sm btn-outline-danger btn-wave"
                                            wire:click="deleteProduct({{ $product->id }})"
                                            wire:confirm="Are you sure you want to delete this product?">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No products found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($products->hasPages())
        <div class="card-footer">
            {{ $products->links() }}
        </div>
        @endif
    </div>
</div>
