<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlanFeature>
 */
class PlanFeatureFactory extends Factory
{
    protected $model = PlanFeature::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plan_id' => Plan::factory(),
            'feature_key' => fake()->randomElement(['max_users', 'max_products', 'max_warehouses', 'manufacturing_enabled', 'crm_enabled']),
            'feature_value' => fake()->randomElement(['3', '10', '500', 'true', 'false', 'unlimited']),
        ];
    }
}
