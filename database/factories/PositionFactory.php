<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\Member;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Position>
 */
class PositionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->optional()->dateTimeBetween('-1 year', 'now');

        return [
            'club_id' => Club::factory(),
            'member_id' => fn (array $attributes): int => Member::factory()->create([
                'club_id' => $attributes['club_id'],
            ])->getKey(),
            'name' => fake()->unique()->words(2, true),
            'sort_order' => fake()->numberBetween(0, 20),
            'start_date' => $startDate,
            'end_date' => $startDate === null
                ? null
                : fake()->optional()->dateTimeBetween($startDate, '+1 year'),
        ];
    }
}
