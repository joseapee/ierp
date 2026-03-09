<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int|null $tenant_id
 * @property int $product_id
 * @property string $name
 * @property string $version
 * @property string|null $description
 * @property float $yield_quantity
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $effective_date
 * @property \Illuminate\Support\Carbon|null $expiry_date
 * @property string|null $notes
 */
class Bom extends Model
{
    /** @use HasFactory<\Database\Factories\BomFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'name',
        'version',
        'description',
        'yield_quantity',
        'status',
        'effective_date',
        'expiry_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'yield_quantity' => 'decimal:4',
            'effective_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BomItem::class)->orderBy('sort_order');
    }

    public function productionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class);
    }
}
