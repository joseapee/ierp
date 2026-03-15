<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'slug' => fake()->unique()->slug(2),
            'domain' => null,
            'plan' => fake()->randomElement(['starter', 'pro', 'enterprise']),
            'status' => 'active',
            'settings' => [],
        ];
    }

    public function withPlan(?Plan $plan = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'plan_id' => $plan?->id ?? Plan::factory(),
        ]);
    }

    public function withSubscription(string $status = 'active'): static
    {
        return $this->afterCreating(function (Tenant $tenant) use ($status): void {
            $plan = $tenant->plan_id
                ? Plan::find($tenant->plan_id)
                : Plan::factory()->create();

            if (! $tenant->plan_id) {
                $tenant->update(['plan_id' => $plan->id]);
            }

            $factory = Subscription::factory()
                ->for($tenant)
                ->for($plan);

            match ($status) {
                'trial' => $factory->trial()->create(),
                'expired' => $factory->expired()->create(),
                'suspended' => $factory->suspended()->create(),
                'cancelled' => $factory->cancelled()->create(),
                'grace_period' => $factory->gracePeriod()->create(),
                default => $factory->active()->create(),
            };
        });
    }

    public function setupComplete(): static
    {
        return $this->state(fn (array $attributes): array => [
            'setup_completed_at' => now(),
        ]);
    }

    public function onboardingComplete(): static
    {
        return $this->state(fn (array $attributes): array => [
            'setup_completed_at' => now(),
            'onboarding_completed_at' => now(),
        ]);
    }
}
