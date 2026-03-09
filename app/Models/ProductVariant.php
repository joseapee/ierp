<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $product_id
 * @property string $sku
 * @property string|null $barcode
 * @property string $name
 * @property float|null $cost_price_override
 * @property float|null $sell_price_override
 * @property string|null $image
 * @property bool $is_active
 * @property int $sort_order
 */
class ProductVariant extends Model
{
    /** @use HasFactory<\Database\Factories\ProductVariantFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'barcode',
        'name',
        'cost_price_override',
        'sell_price_override',
        'image',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'cost_price_override' => 'decimal:4',
            'sell_price_override' => 'decimal:4',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductAttributeValue::class,
            'product_variant_attributes',
            'product_variant_id',
            'product_attribute_value_id'
        )->withPivot('product_attribute_id');
    }

    public function stockBatches(): HasMany
    {
        return $this->hasMany(StockBatch::class);
    }

    public function stockLedgerEntries(): HasMany
    {
        return $this->hasMany(StockLedger::class);
    }

    /**
     * Get the effective cost price (override or parent product price).
     */
    public function getEffectiveCostPriceAttribute(): string
    {
        return $this->cost_price_override ?? $this->product->cost_price;
    }

    /**
     * Get the effective sell price (override or parent product price).
     */
    public function getEffectiveSellPriceAttribute(): string
    {
        return $this->sell_price_override ?? $this->product->sell_price;
    }
}
