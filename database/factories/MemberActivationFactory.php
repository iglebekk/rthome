<?php

namespace Database\Factories;

use App\Models\MemberActivation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemberActivation>
 */
class MemberActivationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'token' => hash('sha256', fake()->uuid()),
            'expires_at' => now()->addHour(),
            'used_at' => null,
        ];
    }
}
