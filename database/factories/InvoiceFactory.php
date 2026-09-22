<?php

namespace Database\Factories;

use App\Enums\InvoiceDocumentType;
use App\Enums\InvoiceStatus;
use App\Models\Club;
use App\Models\Invoice;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
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
            'invoice_creation_id' => null,
            'member_id' => Member::factory(),
            'credited_invoice_id' => null,
            'document_type' => InvoiceDocumentType::Invoice,
            'status' => InvoiceStatus::Issued,
            'number' => fake()->unique()->numberBetween(10001, 99999),
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'issued_at' => now(),
            'club_name' => fake()->company(),
            'club_organization_number' => fake()->numerify('#########'),
            'club_account_number' => fake()->numerify('###########'),
            'recipient_name' => fake()->name(),
            'recipient_company_name' => null,
            'recipient_organization_number' => null,
            'recipient_address' => null,
            'recipient_postal_code' => null,
            'recipient_city' => null,
            'recipient_email' => fake()->safeEmail(),
            'net_total_ore' => 8000,
            'vat_total_ore' => 2000,
            'gross_total_ore' => 10000,
        ];
    }
}
