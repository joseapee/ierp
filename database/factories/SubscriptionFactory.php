<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'plan_id' => Plan::factory(),
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'auto_renew' => true,
        ];
    }

    public function trial(int $days = 14): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'trial',
            'trial_ends_at' => now()->addDays($days),
            'ends_at' => now()->addDays($days),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'past_due',
            'starts_at' => now()->subDays(30),
            'ends_at' => now()->subDay(),
        ]);
    }

    public function gracePeriod(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'grace_period',
            'starts_at' => now()->subDays(30),
            'ends_at' => now()->subDay(),
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'suspended',
            'starts_at' => now()->subDays(60),
            'ends_at' => now()->subDays(30),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'starts_at' => now()->subDays(30),
            'ends_at' => now()->subDay(),
        ]);
    }

    public function annual(): static
    {
        return $this->state(fn (array $attributes): array => [
            'billing_cycle' => 'annual',
            'starts_at' => now(),
            'ends_at' => now()->addDays(365),
        ]);
    }
}
