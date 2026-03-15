<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $tenant_id
 * @property int $plan_id
 * @property string $billing_cycle
 * @property string $status
 * @property Carbon|null $trial_ends_at
 * @property Carbon $starts_at
 * @property Carbon $ends_at
 * @property Carbon|null $cancelled_at
 * @property bool $auto_renew
 * @property string|null $paystack_subscription_code
 * @property string|null $paystack_customer_code
 * @property string|null $paystack_authorization_code
 */
class Subscription extends Model
{
    /** @use HasFactory<\Database\Factories\SubscriptionFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'billing_cycle',
        'status',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'cancelled_at',
        'auto_renew',
        'paystack_subscription_code',
        'paystack_customer_code',
        'paystack_authorization_code',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'auto_renew' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @param  Builder<Subscription>  $query
     * @return Builder<Subscription>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['active', 'trial']);
    }

    /**
     * @param  Builder<Subscription>  $query
     * @return Builder<Subscription>
     */
    public function scopeTrial(Builder $query): Builder
    {
        return $query->where('status', 'trial');
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trial', 'grace_period']);
    }

    public function isTrial(): bool
    {
        return $this->status === 'trial';
    }

    public function isExpired(): bool
    {
        return $this->ends_at->isPast() && ! $this->isActive();
    }

    public function isInGracePeriod(): bool
    {
        return $this->status === 'grace_period';
    }

    public function daysRemaining(): int
    {
        if ($this->isTrial() && $this->trial_ends_at) {
            return max(0, (int) now()->diffInDays($this->trial_ends_at, false));
        }

        return max(0, (int) now()->diffInDays($this->ends_at, false));
    }
}
