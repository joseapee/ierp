<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $production_order_id
 * @property string|null $current_stage
 * @property float $quantity
 * @property float $unit_cost
 * @property float $total_cost
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $last_updated_at
 */
class WipInventory extends Model
{
    /** @use HasFactory<\Database\Factories\WipInventoryFactory> */
    use HasFactory;

    protected $table = 'wip_inventory';

    protected $fillable = [
        'production_order_id',
        'current_stage',
        'quantity',
        'unit_cost',
        'total_cost',
        'status',
        'last_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_cost' => 'decimal:4',
            'total_cost' => 'decimal:4',
            'last_updated_at' => 'datetime',
        ];
    }

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }
}
