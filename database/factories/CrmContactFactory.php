<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CrmContact;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CrmContact> */
class CrmContactFactory extends Factory
{
    protected $model = CrmContact::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'customer_id' => null,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'job_title' => fake()->jobTitle(),
            'department' => fake()->randomElement(['Sales', 'Marketing', 'Finance', 'Operations', 'IT']),
            'is_primary' => false,
            'notes' => null,
        ];
    }

    public function primary(): static
    {
        return $this->state(['is_primary' => true]);
    }
}
