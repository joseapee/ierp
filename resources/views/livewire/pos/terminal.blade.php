<div>
    @section('title', 'POS Terminal')

    <x-page-header title="POS Terminal" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'POS Terminal'],
    ]">
        {{-- <x-slot:actions>
            @can('brands.create')
                <button class="btn btn-primary btn-wave"
                        wire:click="$dispatch('openBrandFormModal')">
                    <i class="ri-add-line me-1"></i> Button
                </button>
            @endcan
        </x-slot:actions> --}}
    </x-page-header>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center">
                    <input type="text" class="form-control me-2" placeholder="Scan barcode or search product..." wire:model.debounce.300ms="search">
                </div>
                <div class="card-body p-2">
                    <div class="row g-2">
                        @foreach($products as $product)
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="card h-100 product-tile" wire:click="selectProduct({{ $product->id }})" style="cursor:pointer;">
                                    <img src="{{ $product->image ?? asset('images/no-image.png') }}" class="card-img-top" alt="{{ $product->name }}">
                                    <div class="card-body p-2">
                                        <div class="fw-semibold">{{ $product->name }}</div>
                                        <div class="text-muted small">{{ $product->sku }}</div>
                                        <div class="fw-bold">{{ format_currency($product->sell_price) }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">Cart</div>
                <div class="card-body p-2">
                    @forelse($cart as $index => $item)
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $item['name'] }}</div>
                                <div class="text-muted small">{{ $item['sku'] }}</div>
                                <input type="number" min="1" step="1" class="form-control form-control-sm w-50 d-inline-block" wire:model.lazy="cart.{{ $index }}.quantity">
                            </div>
                            <div class="ms-2">
                                <button class="btn btn-sm btn-danger" wire:click="removeFromCart({{ $index }})">&times;</button>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted text-center">Cart is empty</div>
                    @endforelse
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <span>Subtotal:</span>
                        <span>{{ format_currency($this->subtotal) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Tax:</span>
                        <span>{{ format_currency($this->taxTotal) }}</span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Total:</span>
                        <span>{{ format_currency($this->grandTotal) }}</span>
                    </div>
                    <button class="btn btn-primary w-100 mt-2" wire:click="openPaymentModal" @if(empty($cart)) disabled @endif>Checkout</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Product Modal --}}
    <x-modal name="product-modal" wire:model="showProductModal">
        <x-slot name="title">Add Product</x-slot>
        @if($selectedProductId)
            @php $product = $products->firstWhere('id', $selectedProductId); @endphp
            <div>
                <div class="fw-bold mb-2">{{ $product->name }}</div>
                <div>Price: {{ format_currency($product->sell_price) }}</div>
                <div>SKU: {{ $product->sku }}</div>
                <div class="mt-2">
                    <button class="btn btn-success" wire:click="addToCart({{ $product->id }})">Add to Cart</button>
                    <button class="btn btn-light" wire:click="$set('showProductModal', false)">Cancel</button>
                </div>
            </div>
        @endif
    </x-modal>

    {{-- Payment Modal --}}
    <x-modal name="payment-modal" wire:model="showPaymentModal">
        <x-slot name="title">Payment</x-slot>
        <div class="mb-2">
            <label>Customer</label>
            <select class="form-select" wire:model="customerId">
                <option value="">Walk-in</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-2">
            <label>Payment Type</label>
            <select class="form-select" wire:model="paymentType">
                <option value="cash">Cash</option>
                <option value="card">Card</option>
                <option value="split">Split</option>
            </select>
        </div>
        <div class="mb-2">
            <label>Amount Paid</label>
            <input type="number" min="0" step="0.01" class="form-control" wire:model="amountPaid">
        </div>
        <div class="d-flex justify-content-between fw-bold mb-2">
            <span>Total:</span>
            <span>{{ format_currency($this->grandTotal) }}</span>
        </div>
        <button class="btn btn-success w-100" wire:click="processPayment">Complete Sale</button>
        <button class="btn btn-light w-100 mt-2" wire:click="$set('showPaymentModal', false)">Cancel</button>
    </x-modal>

    {{-- Receipt Modal --}}
    <x-modal name="receipt-modal" wire:model="showReceiptModal">
        <x-slot name="title">Receipt</x-slot>
        @if($order)
            <div>
                <div class="mb-2">Order #: <b>{{ $order->order_number }}</b></div>
                <div>Date: {{ format_datetime($order->order_date) }}</div>
                <div class="mt-2">
                    <b>Items:</b>
                    <ul>
                        @foreach($order->items as $item)
                            <li>{{ $item->product->name }} x {{ $item->quantity }} - {{ format_currency($item->unit_price) }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="fw-bold mt-2">Total: {{ format_currency($order->total_amount) }}</div>
                <button class="btn btn-primary w-100 mt-2" onclick="window.print()">Print Receipt</button>
                <button class="btn btn-light w-100 mt-2" wire:click="closeReceiptModal">Close</button>
            </div>
        @endif
    </x-modal>
</div>
