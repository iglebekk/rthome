<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\Event;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = CarbonImmutable::instance(fake()->dateTimeBetween('+1 day', '+2 months'));
        $endsAt = $startsAt->addHours(fake()->numberBetween(1, 8));

        return [
            'club_id' => Club::factory(),
            'name' => fake()->sentence(3),
            'location' => fake()->optional()->city(),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'registration_url' => fake()->optional()->url(),
            'short_description' => fake()->optional()->text(300),
            'image_path' => null,
        ];
    }
}
