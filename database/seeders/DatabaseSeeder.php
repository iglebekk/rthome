<?php

namespace Database\Seeders;

use App\Actions\CreateInvoicesAction;
use App\Actions\CreditInvoiceAction;
use App\Actions\MarkInvoicePaidAction;
use App\Enums\VatTreatment;
use App\Models\Club;
use App\Models\Event;
use App\Models\Member;
use App\Models\Position;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $club = Club::factory()->create([
            'name' => 'Demo Club',
            'organization_number' => '912345678',
            'invoice_name' => 'Demo Club AS',
            'account_number' => '12345678903',
        ]);

        $member = Member::factory()
            ->for($club)
            ->for($user)
            ->create([
                'name' => $user->name,
                'email' => $user->email,
            ]);

        $members = Member::factory()->count(4)->for($club)->create();

        $invoiceRecipient = $members[0];
        $companyRecipient = $members[1];
        $companyRecipient->update([
            'invoice_company_name' => 'Example Consulting AS',
            'invoice_organization_number' => '987654321',
            'invoice_address' => 'Example Street 1',
            'invoice_postal_code' => '0150',
            'invoice_city' => 'Oslo',
        ]);

        Position::factory()->for($club)->for($member)->create([
            'name' => 'President',
            'sort_order' => 0,
        ]);
        Position::factory()->for($club)->for($members[0])->create([
            'name' => 'Treasurer',
            'sort_order' => 1,
        ]);
        Position::factory()->for($club)->for($members[1])->create([
            'name' => 'Board Member',
            'sort_order' => 2,
        ]);

        Event::factory()->count(3)->for($club)->create();

        $membership = Product::factory()->for($club)->create([
            'name' => 'Annual membership',
            'description' => 'Annual membership for 2026',
            'gross_price_ore' => 50000,
            'vat_treatment' => VatTreatment::Exempt,
        ]);
        $workshop = Product::factory()->for($club)->create([
            'name' => 'Workshop fee',
            'description' => 'Participation in the autumn workshop',
            'gross_price_ore' => 125000,
            'vat_treatment' => VatTreatment::Standard,
        ]);
        Product::factory()->for($club)->create([
            'name' => 'Printed materials',
            'description' => 'Printed course materials',
            'gross_price_ore' => 35000,
            'vat_treatment' => VatTreatment::Reduced12,
        ]);
        Product::factory()->for($club)->create([
            'name' => 'Archived product',
            'gross_price_ore' => 25000,
            'is_active' => false,
        ]);

        $createInvoices = app(CreateInvoicesAction::class);
        $issuedCreation = $createInvoices->handle($club, [
            'intent' => 'issue',
            'submission_token' => (string) Str::uuid(),
            'invoice_date' => now()->subWeek()->toDateString(),
            'due_date' => now()->addWeek()->toDateString(),
            'recipients' => [$invoiceRecipient->getKey(), $companyRecipient->getKey()],
            'lines' => [
                ['product_id' => $membership->getKey(), 'quantity' => '1.00'],
                ['product_id' => $workshop->getKey(), 'quantity' => '1.00'],
            ],
        ]);

        $issuedInvoices = $issuedCreation->invoices;
        $issuedInvoices[0]->update([
            'email_sent_at' => now()->subDays(6),
            'email_send_attempts' => 1,
        ]);
        app(MarkInvoicePaidAction::class)->handle(
            $issuedInvoices[0],
            $issuedInvoices[0]->gross_total_ore,
            now()->subDay(),
        );
        app(CreditInvoiceAction::class)->handle($issuedInvoices[1]);

        $createInvoices->handle($club, [
            'intent' => 'draft',
            'submission_token' => (string) Str::uuid(),
            'invoice_date' => now()->addWeek()->toDateString(),
            'due_date' => now()->addWeeks(3)->toDateString(),
            'recipients' => [$member->getKey()],
            'lines' => [
                ['product_id' => $membership->getKey(), 'quantity' => '1.00'],
            ],
        ]);
    }
}
