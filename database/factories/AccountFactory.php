<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Account> */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $type = fake()->randomElement(['asset', 'liability', 'equity', 'revenue', 'expense']);

        return [
            'tenant_id' => null,
            'parent_id' => null,
            'code' => (string) fake()->unique()->numberBetween(1000, 9999),
            'name' => fake()->unique()->words(2, true),
            'type' => $type,
            'sub_type' => null,
            'normal_balance' => in_array($type, ['asset', 'expense']) ? 'debit' : 'credit',
            'is_system' => false,
            'is_active' => true,
            'description' => null,
            'currency_code' => 'NGN',
        ];
    }

    public function asset(): static
    {
        return $this->state(['type' => 'asset', 'normal_balance' => 'debit']);
    }

    public function liability(): static
    {
        return $this->state(['type' => 'liability', 'normal_balance' => 'credit']);
    }

    public function equity(): static
    {
        return $this->state(['type' => 'equity', 'normal_balance' => 'credit']);
    }

    public function revenue(): static
    {
        return $this->state(['type' => 'revenue', 'normal_balance' => 'credit']);
    }

    public function expense(): static
    {
        return $this->state(['type' => 'expense', 'normal_balance' => 'debit']);
    }

    public function system(): static
    {
        return $this->state(['is_system' => true]);
    }
}
