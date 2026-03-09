<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\UnitConversion;
use App\Models\UnitOfMeasure;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<UnitConversion> */
class UnitConversionFactory extends Factory
{
    protected $model = UnitConversion::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'from_unit_id' => UnitOfMeasure::factory(),
            'to_unit_id' => UnitOfMeasure::factory(),
            'factor' => fake()->randomFloat(4, 0.001, 1000),
        ];
    }
}
