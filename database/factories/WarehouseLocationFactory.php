<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WarehouseLocation> */
class WarehouseLocationFactory extends Factory
{
    protected $model = WarehouseLocation::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'warehouse_id' => Warehouse::factory(),
            'name' => 'Aisle '.fake()->randomLetter().' / Shelf '.fake()->numberBetween(1, 10),
            'code' => strtoupper(fake()->unique()->bothify('LOC-??-##')),
            'parent_id' => null,
            'is_active' => true,
        ];
    }
}
