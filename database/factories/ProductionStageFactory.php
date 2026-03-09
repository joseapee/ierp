<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductionStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProductionStage> */
class ProductionStageFactory extends Factory
{
    protected $model = ProductionStage::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'name' => fake()->unique()->words(2, true),
            'code' => strtoupper(fake()->unique()->bothify('STG-###')),
            'description' => fake()->optional()->sentence(),
            'industry_type' => null,
            'sort_order' => 0,
            'estimated_duration_minutes' => fake()->optional()->numberBetween(15, 480),
            'is_active' => true,
        ];
    }
}
