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
 * @property int $sales_order_id
 * @property int $product_id
 * @property int|null $product_variant_id
 * @property string|null $description
 * @property float $quantity
 * @property float $unit_price
 * @property float $discount_percent
 * @property float $tax_rate
 * @property float $tax_amount
 * @property float $total
 * @property float $quantity_fulfilled
 * @property int $warehouse_id
 */
class SalesOrderItem extends Model
{
    /** @use HasFactory<\Database\Factories\SalesOrderItemFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'sales_order_id',
        'product_id',
        'product_variant_id',
        'description',
        'quantity',
        'unit_price',
        'discount_percent',
        'tax_rate',
        'tax_amount',
        'total',
        'quantity_fulfilled',
        'warehouse_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_price' => 'decimal:4',
            'discount_percent' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:4',
            'total' => 'decimal:4',
            'quantity_fulfilled' => 'decimal:4',
        ];
    }

    public function getRemainingQuantityAttribute(): float
    {
        return (float) $this->quantity - (float) $this->quantity_fulfilled;
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
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
