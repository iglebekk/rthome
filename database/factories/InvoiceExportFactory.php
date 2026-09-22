<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\InvoiceExport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceExport>
 */
class InvoiceExportFactory extends Factory
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
            'user_id' => User::factory(),
            'invoice_ids' => [],
            'batch_id' => null,
            'status' => 'pending',
            'archive_path' => null,
            'expires_at' => now()->addWeek(),
        ];
    }
}
