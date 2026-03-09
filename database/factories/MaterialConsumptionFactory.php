<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MaterialConsumption;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MaterialConsumption> */
class MaterialConsumptionFactory extends Factory
{
    protected $model = MaterialConsumption::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $planned = fake()->randomFloat(4, 1, 50);

        return [
            'production_order_id' => ProductionOrder::factory(),
            'product_id' => Product::factory(),
            'product_variant_id' => null,
            'warehouse_id' => Warehouse::factory(),
            'stock_batch_id' => null,
            'planned_quantity' => $planned,
            'actual_quantity' => $planned,
            'unit_cost' => fake()->randomFloat(4, 1, 200),
            'total_cost' => 0,
            'wastage_quantity' => 0,
            'consumed_at' => now(),
            'consumed_by' => null,
            'notes' => null,
        ];
    }
}
