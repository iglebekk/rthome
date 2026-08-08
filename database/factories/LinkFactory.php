<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\Link;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Link>
 */
class LinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'club_id' => Club::factory(),
            'name' => fake()->sentence(3),
            'url' => fake()->url(),
            'is_pinned' => false,
        ];
    }
}
