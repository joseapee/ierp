<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductionOrder;
use App\Models\ProductionStage;
use App\Models\ProductionTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProductionTask> */
class ProductionTaskFactory extends Factory
{
    protected $model = ProductionTask::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'production_order_id' => ProductionOrder::factory(),
            'current_stage_id' => ProductionStage::factory(),
            'task_number' => strtoupper(fake()->unique()->bothify('TSK-####')),
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'status' => 'pending',
            'sort_order' => 0,
            'estimated_duration_minutes' => fake()->optional()->numberBetween(15, 240),
            'actual_duration_minutes' => null,
            'started_at' => null,
            'completed_at' => null,
            'assigned_to' => null,
            'notes' => null,
        ];
    }
}
