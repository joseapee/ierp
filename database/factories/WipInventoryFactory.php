<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductionOrder;
use App\Models\WipInventory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WipInventory> */
class WipInventoryFactory extends Factory
{
    protected $model = WipInventory::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'production_order_id' => ProductionOrder::factory(),
            'current_stage' => null,
            'quantity' => fake()->randomFloat(4, 1, 100),
            'unit_cost' => fake()->randomFloat(4, 1, 500),
            'total_cost' => 0,
            'status' => 'in_progress',
            'last_updated_at' => now(),
        ];
    }
}
