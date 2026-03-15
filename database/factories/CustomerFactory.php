<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Customer> */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'name' => fake()->company(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'tax_id' => fake()->optional()->numerify('TIN-########'),
            'billing_address_line1' => fake()->streetAddress(),
            'billing_address_line2' => null,
            'billing_city' => fake()->city(),
            'billing_state' => fake()->state(),
            'billing_postal_code' => fake()->postcode(),
            'billing_country' => 'NG',
            'shipping_address_line1' => fake()->streetAddress(),
            'shipping_address_line2' => null,
            'shipping_city' => fake()->city(),
            'shipping_state' => fake()->state(),
            'shipping_postal_code' => fake()->postcode(),
            'shipping_country' => 'NG',
            'credit_limit' => fake()->randomFloat(4, 50000, 5000000),
            'payment_terms' => fake()->randomElement([15, 30, 45, 60]),
            'currency_code' => 'NGN',
            'is_active' => true,
            'notes' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
