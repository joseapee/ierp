<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<JournalLine> */
class JournalLineFactory extends Factory
{
    protected $model = JournalLine::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $amount = fake()->randomFloat(4, 10, 10000);

        return [
            'tenant_id' => null,
            'journal_entry_id' => JournalEntry::factory(),
            'account_id' => Account::factory(),
            'description' => null,
            'debit' => $amount,
            'credit' => 0,
        ];
    }

    public function debit(float $amount): static
    {
        return $this->state(['debit' => $amount, 'credit' => 0]);
    }

    public function credit(float $amount): static
    {
        return $this->state(['debit' => 0, 'credit' => $amount]);
    }
}
