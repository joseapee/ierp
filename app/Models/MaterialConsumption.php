<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $production_order_id
 * @property int $product_id
 * @property int|null $product_variant_id
 * @property int $warehouse_id
 * @property int|null $stock_batch_id
 * @property float $planned_quantity
 * @property float $actual_quantity
 * @property float $unit_cost
 * @property float $total_cost
 * @property float $wastage_quantity
 * @property \Illuminate\Support\Carbon|null $consumed_at
 * @property int|null $consumed_by
 * @property string|null $notes
 */
class MaterialConsumption extends Model
{
    /** @use HasFactory<\Database\Factories\MaterialConsumptionFactory> */
    use HasFactory;

    protected $fillable = [
        'production_order_id',
        'product_id',
        'product_variant_id',
        'warehouse_id',
        'stock_batch_id',
        'planned_quantity',
        'actual_quantity',
        'unit_cost',
        'total_cost',
        'wastage_quantity',
        'consumed_at',
        'consumed_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'planned_quantity' => 'decimal:4',
            'actual_quantity' => 'decimal:4',
            'unit_cost' => 'decimal:4',
            'total_cost' => 'decimal:4',
            'wastage_quantity' => 'decimal:4',
            'consumed_at' => 'datetime',
        ];
    }

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
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

    public function stockBatch(): BelongsTo
    {
        return $this->belongsTo(StockBatch::class);
    }

    public function consumedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consumed_by');
    }
}
