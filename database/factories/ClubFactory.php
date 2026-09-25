<?php

namespace Database\Factories;

use App\Models\Club;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Club>
 */
class ClubFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company().' Club',
            'organization_number' => null,
            'invoice_name' => null,
            'account_number' => null,
            'locale' => 'nb',
            'invoice_sequence' => 10000,
            'public_events_token' => null,
            'public_events_enabled' => false,
        ];
    }

    public function withPublicEventsSharing(?string $token = null): static
    {
        return $this->state(fn (): array => [
            'public_events_token' => $token ?? Str::random(64),
            'public_events_enabled' => true,
        ]);
    }
}
