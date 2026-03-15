<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $plan_id
 * @property string $feature_key
 * @property string $feature_value
 */
class PlanFeature extends Model
{
    /** @use HasFactory<\Database\Factories\PlanFeatureFactory> */
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'feature_key',
        'feature_value',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
