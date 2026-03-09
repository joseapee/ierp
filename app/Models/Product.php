<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int|null $tenant_id
 * @property int|null $category_id
 * @property int|null $brand_id
 * @property int $base_unit_id
 * @property string $name
 * @property string $slug
 * @property string $sku
 * @property string $type
 * @property string|null $description
 * @property string|null $short_description
 * @property string|null $image
 * @property string|null $barcode
 * @property float $cost_price
 * @property float $sell_price
 * @property string $pricing_mode
 * @property float|null $markup_percentage
 * @property float|null $profit_amount
 * @property float $tax_rate
 * @property string $valuation_method
 * @property float $reorder_level
 * @property float $reorder_quantity
 * @property bool $is_active
 * @property bool $is_purchasable
 * @property bool $is_sellable
 * @property bool $is_stockable
 */
class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'brand_id',
        'base_unit_id',
        'name',
        'slug',
        'sku',
        'type',
        'description',
        'short_description',
        'image',
        'barcode',
        'cost_price',
        'sell_price',
        'pricing_mode',
        'markup_percentage',
        'profit_amount',
        'tax_rate',
        'valuation_method',
        'reorder_level',
        'reorder_quantity',
        'is_active',
        'is_purchasable',
        'is_sellable',
        'is_stockable',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:4',
            'sell_price' => 'decimal:4',
            'markup_percentage' => 'decimal:4',
            'profit_amount' => 'decimal:4',
            'tax_rate' => 'decimal:2',
            'reorder_level' => 'decimal:4',
            'reorder_quantity' => 'decimal:4',
            'is_active' => 'boolean',
            'is_purchasable' => 'boolean',
            'is_sellable' => 'boolean',
            'is_stockable' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'base_unit_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(ProductAttribute::class, 'product_attribute_product');
    }

    public function stockBatches(): HasMany
    {
        return $this->hasMany(StockBatch::class);
    }

    public function stockLedgerEntries(): HasMany
    {
        return $this->hasMany(StockLedger::class);
    }

    public function boms(): HasMany
    {
        return $this->hasMany(Bom::class);
    }

    public function activeBom(): HasOne
    {
        return $this->hasOne(Bom::class)->where('status', 'active')->latestOfMany();
    }

    public function getComputedSellPriceAttribute(): string
    {
        $cost = (float) $this->cost_price;

        return match ($this->pricing_mode) {
            'percentage_markup' => number_format($cost * (1 + ((float) $this->markup_percentage / 100)), 4, '.', ''),
            'fixed_profit' => number_format($cost + (float) $this->profit_amount, 4, '.', ''),
            default => $this->sell_price,
        };
    }
}
