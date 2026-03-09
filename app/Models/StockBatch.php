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
 * @property int $product_id
 * @property int|null $product_variant_id
 * @property int $warehouse_id
 * @property int|null $warehouse_location_id
 * @property string|null $batch_number
 * @property string|null $serial_number
 * @property \Illuminate\Support\Carbon|null $manufacturing_date
 * @property \Illuminate\Support\Carbon|null $expiry_date
 * @property float $initial_quantity
 * @property float $remaining_quantity
 * @property float $unit_cost
 * @property string $status
 */
class StockBatch extends Model
{
    /** @use HasFactory<\Database\Factories\StockBatchFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'product_variant_id',
        'warehouse_id',
        'warehouse_location_id',
        'batch_number',
        'serial_number',
        'manufacturing_date',
        'expiry_date',
        'initial_quantity',
        'remaining_quantity',
        'unit_cost',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'manufacturing_date' => 'date',
            'expiry_date' => 'date',
            'initial_quantity' => 'decimal:4',
            'remaining_quantity' => 'decimal:4',
            'unit_cost' => 'decimal:4',
        ];
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

    public function warehouseLocation(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class);
    }
}
