<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $production_order_id
 * @property int|null $current_stage_id
 * @property string|null $task_number
 * @property string $name
 * @property string|null $description
 * @property string $status
 * @property int $sort_order
 * @property int|null $estimated_duration_minutes
 * @property int|null $actual_duration_minutes
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property int|null $assigned_to
 * @property string|null $notes
 */
class ProductionTask extends Model
{
    /** @use HasFactory<\Database\Factories\ProductionTaskFactory> */
    use HasFactory;

    protected $fillable = [
        'production_order_id',
        'current_stage_id',
        'task_number',
        'name',
        'description',
        'status',
        'sort_order',
        'estimated_duration_minutes',
        'actual_duration_minutes',
        'started_at',
        'completed_at',
        'assigned_to',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(ProductionStage::class, 'current_stage_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
