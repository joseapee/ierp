<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $tenant_id
 * @property string $type
 * @property string $subject
 * @property string|null $description
 * @property string $related_to_type
 * @property int $related_to_id
 * @property int|null $assigned_to
 * @property \Illuminate\Support\Carbon $due_date
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property string $status
 * @property string|null $notes
 */
class CrmActivity extends Model
{
    /** @use HasFactory<\Database\Factories\CrmActivityFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'type',
        'subject',
        'description',
        'related_to_type',
        'related_to_id',
        'assigned_to',
        'due_date',
        'completed_at',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'pending')->where('due_date', '<', now());
    }
}
