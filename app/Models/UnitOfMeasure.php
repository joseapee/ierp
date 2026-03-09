<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int|null $tenant_id
 * @property string $name
 * @property string $abbreviation
 * @property string $type
 * @property bool $is_base_unit
 * @property bool $is_active
 */
class UnitOfMeasure extends Model
{
    /** @use HasFactory<\Database\Factories\UnitOfMeasureFactory> */
    use BelongsToTenant, HasFactory;

    protected $table = 'units_of_measure';

    protected $fillable = [
        'tenant_id',
        'name',
        'abbreviation',
        'type',
        'is_base_unit',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_base_unit' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function conversionsFrom(): HasMany
    {
        return $this->hasMany(UnitConversion::class, 'from_unit_id');
    }

    public function conversionsTo(): HasMany
    {
        return $this->hasMany(UnitConversion::class, 'to_unit_id');
    }
}
