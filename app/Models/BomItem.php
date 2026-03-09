<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $bom_id
 * @property int $product_id
 * @property int|null $product_variant_id
 * @property float $quantity
 * @property float $unit_cost
 * @property float $wastage_percentage
 * @property int $sort_order
 * @property string|null $notes
 */
class BomItem extends Model
{
    /** @use HasFactory<\Database\Factories\BomItemFactory> */
    use HasFactory;

    protected $fillable = [
        'bom_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'unit_cost',
        'wastage_percentage',
        'sort_order',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_cost' => 'decimal:4',
            'wastage_percentage' => 'decimal:2',
        ];
    }

    public function bom(): BelongsTo
    {
        return $this->belongsTo(Bom::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
