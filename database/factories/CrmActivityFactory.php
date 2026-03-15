<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CrmActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CrmActivity> */
class CrmActivityFactory extends Factory
{
    protected $model = CrmActivity::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'type' => fake()->randomElement(['call', 'meeting', 'email', 'follow_up', 'demo', 'site_visit']),
            'subject' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'related_to_type' => 'Lead',
            'related_to_id' => 1,
            'assigned_to' => null,
            'due_date' => fake()->dateTimeBetween('now', '+2 weeks'),
            'completed_at' => null,
            'status' => 'pending',
            'notes' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }
}
