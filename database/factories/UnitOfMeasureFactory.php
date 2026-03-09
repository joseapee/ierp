<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\UnitOfMeasure;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<UnitOfMeasure> */
class UnitOfMeasureFactory extends Factory
{
    protected $model = UnitOfMeasure::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'name' => fake()->unique()->word(),
            'abbreviation' => fake()->unique()->lexify('??'),
            'type' => fake()->randomElement(['weight', 'length', 'volume', 'area', 'quantity', 'time']),
            'is_base_unit' => false,
            'is_active' => true,
        ];
    }
}
