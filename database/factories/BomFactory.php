<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Bom;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Bom> */
class BomFactory extends Factory
{
    protected $model = Bom::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'product_id' => Product::factory(),
            'name' => fake()->words(3, true).' BOM',
            'version' => '1.0',
            'description' => fake()->optional()->sentence(),
            'yield_quantity' => 1,
            'status' => 'draft',
            'effective_date' => null,
            'expiry_date' => null,
            'notes' => null,
        ];
    }

    public function active(): static
    {
        return $this->state([
            'status' => 'active',
            'effective_date' => now(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(['status' => 'inactive']);
    }
}
