<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockBatch;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<StockBatch> */
class StockBatchFactory extends Factory
{
    protected $model = StockBatch::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $qty = fake()->randomFloat(4, 10, 500);

        return [
            'tenant_id' => null,
            'product_id' => Product::factory(),
            'product_variant_id' => null,
            'warehouse_id' => Warehouse::factory(),
            'warehouse_location_id' => null,
            'batch_number' => strtoupper(fake()->bothify('BATCH-####-??')),
            'serial_number' => null,
            'manufacturing_date' => fake()->optional()->dateTimeBetween('-1 year', 'now'),
            'expiry_date' => fake()->optional()->dateTimeBetween('now', '+2 years'),
            'initial_quantity' => $qty,
            'remaining_quantity' => $qty,
            'unit_cost' => fake()->randomFloat(4, 1, 200),
            'status' => 'available',
        ];
    }
}
