<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CrmCommunication;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CrmCommunication> */
class CrmCommunicationFactory extends Factory
{
    protected $model = CrmCommunication::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'customer_id' => null,
            'contact_id' => null,
            'lead_id' => null,
            'type' => fake()->randomElement(['email', 'call', 'sms', 'whatsapp', 'meeting', 'note']),
            'subject' => fake()->sentence(4),
            'message' => fake()->paragraph(3),
            'created_by' => null,
        ];
    }
}
