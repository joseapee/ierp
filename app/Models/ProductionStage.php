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
 * @property string $code
 * @property string|null $description
 * @property string|null $industry_type
 * @property int $sort_order
 * @property int|null $estimated_duration_minutes
 * @property bool $is_active
 */
class ProductionStage extends Model
{
    /** @use HasFactory<\Database\Factories\ProductionStageFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'description',
        'industry_type',
        'sort_order',
        'estimated_duration_minutes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProductionTask::class, 'current_stage_id');
    }
}
