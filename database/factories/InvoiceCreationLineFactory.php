<?php

namespace Database\Factories;

use App\Models\InvoiceCreation;
use App\Models\InvoiceCreationLine;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceCreationLine>
 */
class InvoiceCreationLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_creation_id' => InvoiceCreation::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->randomElement(['1.00', '2.00', '3.50']),
            'position' => 0,
        ];
    }
}
