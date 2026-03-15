<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $domain
 * @property int|null $plan_id
 * @property string $plan
 * @property string $status
 * @property array|null $settings
 * @property \Illuminate\Support\Carbon|null $trial_ends_at
 * @property \Illuminate\Support\Carbon|null $subscription_ends_at
 * @property \Illuminate\Support\Carbon|null $onboarding_completed_at
 * @property \Illuminate\Support\Carbon|null $setup_completed_at
 * @property string|null $industry
 * @property string $currency
 * @property string $timezone
 * @property string|null $country
 * @property string|null $city
 * @property string|null $address
 * @property string|null $phone
 */
class Tenant extends Model
{
    /** @use HasFactory<\Database\Factories\TenantFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'plan_id',
        'plan',
        'status',
        'settings',
        'trial_ends_at',
        'subscription_ends_at',
        'onboarding_completed_at',
        'setup_completed_at',
        'industry',
        'currency',
        'timezone',
        'country',
        'city',
        'address',
        'phone',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'trial_ends_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
            'onboarding_completed_at' => 'datetime',
            'setup_completed_at' => 'datetime',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function currentPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->whereIn('status', ['active', 'trial', 'grace_period'])
            ->latest();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isOnTrial(): bool
    {
        $subscription = $this->activeSubscription;

        return $subscription && $subscription->isTrial();
    }

    public function subscriptionStatus(): ?string
    {
        return $this->activeSubscription?->status;
    }

    public function hasFeature(string $key): bool
    {
        $plan = $this->currentPlan;

        if (! $plan) {
            return false;
        }

        return $plan->hasFeature($key);
    }

    public function getFeatureLimit(string $key): ?int
    {
        $plan = $this->currentPlan;

        if (! $plan) {
            return 0;
        }

        $value = $plan->getFeature($key);

        if ($value === null || $value === 'false') {
            return 0;
        }

        if ($value === 'unlimited') {
            return null;
        }

        return (int) $value;
    }
}
