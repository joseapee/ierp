<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StockAdjustment;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<StockAdjustment> */
class StockAdjustmentFactory extends Factory
{
    protected $model = StockAdjustment::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'warehouse_id' => Warehouse::factory(),
            'adjustment_number' => strtoupper(fake()->unique()->bothify('ADJ-####-??')),
            'reason' => fake()->randomElement(['Damaged goods', 'Stock count correction', 'Expired items', 'Missing items']),
            'notes' => fake()->optional()->sentence(),
            'status' => 'draft',
            'adjusted_by' => User::factory(),
            'approved_by' => null,
            'adjusted_at' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state([
            'status' => 'approved',
            'approved_by' => User::factory(),
            'adjusted_at' => now(),
        ]);
    }
}
