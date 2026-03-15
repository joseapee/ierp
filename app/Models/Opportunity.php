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
 * @property string $name
 * @property int $customer_id
 * @property int|null $contact_id
 * @property int $pipeline_stage_id
 * @property float $expected_value
 * @property float $probability
 * @property \Illuminate\Support\Carbon|null $expected_close_date
 * @property int|null $assigned_to
 * @property int|null $sales_order_id
 * @property string|null $lost_reason
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $closed_at
 * @property-read float $weighted_value
 */
class Opportunity extends Model
{
    /** @use HasFactory<\Database\Factories\OpportunityFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'customer_id',
        'contact_id',
        'pipeline_stage_id',
        'expected_value',
        'probability',
        'expected_close_date',
        'assigned_to',
        'sales_order_id',
        'lost_reason',
        'notes',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'expected_value' => 'decimal:4',
            'probability' => 'decimal:2',
            'expected_close_date' => 'date',
            'closed_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(CrmContact::class, 'contact_id');
    }

    public function pipelineStage(): BelongsTo
    {
        return $this->belongsTo(CrmPipelineStage::class, 'pipeline_stage_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function getWeightedValueAttribute(): float
    {
        return (float) $this->expected_value * ((float) $this->probability / 100);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereHas('pipelineStage', fn (Builder $q) => $q->where('is_won', false)->where('is_lost', false));
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWon(Builder $query): Builder
    {
        return $query->whereHas('pipelineStage', fn (Builder $q) => $q->where('is_won', true));
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeLost(Builder $query): Builder
    {
        return $query->whereHas('pipelineStage', fn (Builder $q) => $q->where('is_lost', true));
    }
}
