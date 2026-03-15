<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Opportunity;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Opportunity> */
class OpportunityFactory extends Factory
{
    protected $model = Opportunity::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'name' => fake()->catchPhrase(),
            'customer_id' => null,
            'contact_id' => null,
            'pipeline_stage_id' => null,
            'expected_value' => fake()->randomFloat(4, 50000, 5000000),
            'probability' => fake()->randomFloat(2, 10, 90),
            'expected_close_date' => fake()->dateTimeBetween('now', '+6 months'),
            'assigned_to' => null,
            'sales_order_id' => null,
            'lost_reason' => null,
            'notes' => null,
        ];
    }
}
