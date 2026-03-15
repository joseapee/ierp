<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int|null $tenant_id
 * @property string $lead_name
 * @property string|null $company_name
 * @property string|null $email
 * @property string|null $phone
 * @property string $source
 * @property string|null $industry
 * @property string $status
 * @property int|null $assigned_to
 * @property float $estimated_value
 * @property int $lead_score
 * @property int|null $converted_customer_id
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $converted_at
 */
class Lead extends Model
{
    /** @use HasFactory<\Database\Factories\LeadFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'lead_name',
        'company_name',
        'email',
        'phone',
        'source',
        'industry',
        'status',
        'assigned_to',
        'estimated_value',
        'lead_score',
        'converted_customer_id',
        'notes',
        'converted_at',
    ];

    protected function casts(): array
    {
        return [
            'estimated_value' => 'decimal:4',
            'lead_score' => 'integer',
            'converted_at' => 'datetime',
        ];
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function convertedCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'converted_customer_id');
    }

    public function communications(): HasMany
    {
        return $this->hasMany(CrmCommunication::class, 'lead_id');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['converted', 'lost']);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeConverted(Builder $query): Builder
    {
        return $query->where('status', 'converted');
    }
}
