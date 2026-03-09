<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int|null $tenant_id
 * @property string $order_number
 * @property int $product_id
 * @property int|null $product_variant_id
 * @property int $bom_id
 * @property int $warehouse_id
 * @property float $planned_quantity
 * @property float $completed_quantity
 * @property float $rejected_quantity
 * @property float $unit_cost
 * @property float $total_cost
 * @property string $status
 * @property string $priority
 * @property \Illuminate\Support\Carbon|null $planned_start_date
 * @property \Illuminate\Support\Carbon|null $planned_end_date
 * @property \Illuminate\Support\Carbon|null $actual_start_date
 * @property \Illuminate\Support\Carbon|null $actual_end_date
 * @property string|null $notes
 * @property int|null $created_by
 * @property int|null $completed_by
 */
class ProductionOrder extends Model
{
    /** @use HasFactory<\Database\Factories\ProductionOrderFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'order_number',
        'product_id',
        'product_variant_id',
        'bom_id',
        'warehouse_id',
        'planned_quantity',
        'completed_quantity',
        'rejected_quantity',
        'unit_cost',
        'total_cost',
        'status',
        'priority',
        'planned_start_date',
        'planned_end_date',
        'actual_start_date',
        'actual_end_date',
        'notes',
        'created_by',
        'completed_by',
    ];

    protected function casts(): array
    {
        return [
            'planned_quantity' => 'decimal:4',
            'completed_quantity' => 'decimal:4',
            'rejected_quantity' => 'decimal:4',
            'unit_cost' => 'decimal:4',
            'total_cost' => 'decimal:4',
            'planned_start_date' => 'date',
            'planned_end_date' => 'date',
            'actual_start_date' => 'date',
            'actual_end_date' => 'date',
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

    public function bom(): BelongsTo
    {
        return $this->belongsTo(Bom::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProductionTask::class)->orderBy('sort_order');
    }

    public function materialConsumptions(): HasMany
    {
        return $this->hasMany(MaterialConsumption::class);
    }

    public function wipInventory(): HasOne
    {
        return $this->hasOne(WipInventory::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
