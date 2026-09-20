<?php

namespace Database\Factories;

use App\Enums\VatTreatment;
use App\Models\Club;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
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
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'gross_price_ore' => fake()->numberBetween(1000, 100000),
            'vat_treatment' => VatTreatment::Standard,
            'is_active' => true,
        ];
    }
}
