<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Starter', 'Business', 'Professional', 'Enterprise']);

        return [
            'name' => $name,
            'slug' => strtolower($name),
            'description' => fake()->sentence(),
            'monthly_price' => fake()->randomFloat(2, 10000, 100000),
            'annual_price' => fake()->randomFloat(2, 100000, 1000000),
            'trial_days' => 14,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function trial(int $days = 14): static
    {
        return $this->state(fn (array $attributes): array => [
            'trial_days' => $days,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
