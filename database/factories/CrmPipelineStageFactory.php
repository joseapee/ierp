<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CrmPipelineStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CrmPipelineStage> */
class CrmPipelineStageFactory extends Factory
{
    protected $model = CrmPipelineStage::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'name' => fake()->randomElement(['Qualification', 'Proposal', 'Negotiation', 'Closing']),
            'display_order' => fake()->numberBetween(1, 10) * 10,
            'win_probability' => fake()->randomFloat(2, 10, 90),
            'is_won' => false,
            'is_lost' => false,
            'color' => fake()->hexColor(),
            'is_active' => true,
        ];
    }

    public function won(): static
    {
        return $this->state([
            'name' => 'Closed Won',
            'is_won' => true,
            'win_probability' => 100,
        ]);
    }

    public function lost(): static
    {
        return $this->state([
            'name' => 'Closed Lost',
            'is_lost' => true,
            'win_probability' => 0,
        ]);
    }
}
