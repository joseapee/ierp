<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $tenant_id
 * @property int $purchase_order_id
 * @property int $product_id
 * @property int|null $product_variant_id
 * @property string|null $description
 * @property float $quantity
 * @property float $unit_price
 * @property float $tax_rate
 * @property float $tax_amount
 * @property float $total
 * @property float $quantity_received
 * @property int $warehouse_id
 */
class PurchaseOrderItem extends Model
{
    /** @use HasFactory<\Database\Factories\PurchaseOrderItemFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'purchase_order_id',
        'product_id',
        'product_variant_id',
        'description',
        'quantity',
        'unit_price',
        'tax_rate',
        'tax_amount',
        'total',
        'quantity_received',
        'warehouse_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_price' => 'decimal:4',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:4',
            'total' => 'decimal:4',
            'quantity_received' => 'decimal:4',
        ];
    }

    public function getRemainingQuantityAttribute(): float
    {
        return (float) $this->quantity - (float) $this->quantity_received;
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
