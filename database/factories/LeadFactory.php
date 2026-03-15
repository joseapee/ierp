<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Lead> */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'lead_name' => fake()->name(),
            'company_name' => fake()->company(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'source' => fake()->randomElement(['website', 'social_media', 'email_campaign', 'phone_inquiry', 'walk_in', 'referral', 'manual']),
            'industry' => fake()->randomElement(['Technology', 'Finance', 'Manufacturing', 'Retail', 'Healthcare']),
            'status' => 'new',
            'assigned_to' => null,
            'estimated_value' => fake()->randomFloat(4, 10000, 1000000),
            'lead_score' => fake()->numberBetween(0, 100),
            'notes' => null,
        ];
    }

    public function qualified(): static
    {
        return $this->state(['status' => 'qualified']);
    }

    public function converted(): static
    {
        return $this->state([
            'status' => 'converted',
            'converted_at' => now(),
        ]);
    }
}
