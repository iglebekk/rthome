<?php

namespace Database\Factories;

use App\Enums\VatTreatment;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceLine>
 */
class InvoiceLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'product_id' => Product::factory(),
            'position' => 0,
            'description' => fake()->sentence(3),
            'quantity' => '1.00',
            'gross_unit_price_ore' => 10000,
            'vat_treatment' => VatTreatment::Standard,
            'net_amount_ore' => 8000,
            'vat_amount_ore' => 2000,
            'gross_amount_ore' => 10000,
        ];
    }
}
