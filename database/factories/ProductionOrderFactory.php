<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Bom;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProductionOrder> */
class ProductionOrderFactory extends Factory
{
    protected $model = ProductionOrder::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'order_number' => strtoupper(fake()->unique()->bothify('PO-####-??')),
            'product_id' => Product::factory(),
            'product_variant_id' => null,
            'bom_id' => Bom::factory(),
            'warehouse_id' => Warehouse::factory(),
            'planned_quantity' => fake()->randomFloat(4, 1, 100),
            'completed_quantity' => 0,
            'rejected_quantity' => 0,
            'unit_cost' => 0,
            'total_cost' => 0,
            'status' => 'draft',
            'priority' => 'normal',
            'planned_start_date' => now(),
            'planned_end_date' => now()->addDays(7),
            'actual_start_date' => null,
            'actual_end_date' => null,
            'notes' => null,
            'created_by' => null,
            'completed_by' => null,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(['status' => 'confirmed']);
    }

    public function inProgress(): static
    {
        return $this->state([
            'status' => 'in_progress',
            'actual_start_date' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'actual_start_date' => now()->subDays(3),
                'actual_end_date' => now(),
                'completed_quantity' => $attributes['planned_quantity'],
            ];
        });
    }
}
