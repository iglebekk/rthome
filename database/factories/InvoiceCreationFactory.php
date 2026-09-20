<?php

namespace Database\Factories;

use App\Enums\InvoiceCreationStatus;
use App\Models\Club;
use App\Models\InvoiceCreation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<InvoiceCreation>
 */
class InvoiceCreationFactory extends Factory
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
            'submission_token' => (string) Str::uuid(),
            'status' => InvoiceCreationStatus::Draft,
            'invoice_date' => null,
            'due_date' => null,
            'issued_at' => null,
        ];
    }
}
