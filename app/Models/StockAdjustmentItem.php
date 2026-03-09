<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $stock_adjustment_id
 * @property int $product_id
 * @property int|null $product_variant_id
 * @property int|null $stock_batch_id
 * @property string $type
 * @property float $quantity
 * @property float $unit_cost
 * @property string|null $reason
 */
class StockAdjustmentItem extends Model
{
    /** @use HasFactory<\Database\Factories\StockAdjustmentItemFactory> */
    use HasFactory;

    protected $fillable = [
        'stock_adjustment_id',
        'product_id',
        'product_variant_id',
        'stock_batch_id',
        'type',
        'quantity',
        'unit_cost',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_cost' => 'decimal:4',
        ];
    }

    public function stockAdjustment(): BelongsTo
    {
        return $this->belongsTo(StockAdjustment::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function stockBatch(): BelongsTo
    {
        return $this->belongsTo(StockBatch::class);
    }
}
