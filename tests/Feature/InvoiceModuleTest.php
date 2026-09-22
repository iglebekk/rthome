<?php

use App\Actions\CreateInvoicesAction;
use App\Enums\InvoiceCreationStatus;
use App\Enums\InvoiceDocumentType;
use App\Enums\InvoiceStatus;
use App\Enums\VatTreatment;
use App\Models\Club;
use App\Models\Invoice;
use App\Models\InvoiceCreation;
use App\Models\InvoiceLine;
use App\Models\Member;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

uses(LazilyRefreshDatabase::class);

function invoiceClubContext(bool $withOrganizationNumber = true, bool $withAccountNumber = true): array
{
    $user = User::factory()->create();
    $club = Club::factory()->create([
        'organization_number' => $withOrganizationNumber ? '912345678' : null,
        'account_number' => $withAccountNumber ? '12345678903' : null,
    ]);
    $firstMember = Member::factory()->for($club)->for($user)->create([
        'name' => 'First Member',
        'email' => 'first@example.com',
    ]);
    $secondMember = Member::factory()->for($club)->create([
        'name' => 'Second Member',
        'email' => 'second@example.com',
    ]);

    return [$user, $club, $firstMember, $secondMember];
}

function invoiceCreationPayload(Member $member, Product $product, array $overrides = []): array
{
    return array_replace([
        'intent' => 'issue',
        'submission_token' => (string) Str::uuid(),
        'invoice_date' => '2030-01-01',
        'due_date' => '2030-01-15',
        'recipients' => [$member->getKey()],
        'lines' => [[
            'product_id' => $product->getKey(),
            'quantity' => '2.00',
        ]],
    ], $overrides);
}

test('one request creates a numbered immutable invoice for every recipient', function () {
    [$user, $club, $firstMember, $secondMember] = invoiceClubContext();
    $product = Product::factory()->for($club)->create([
        'name' => 'Membership',
        'description' => 'Annual membership',
        'gross_price_ore' => 50000,
        'vat_treatment' => VatTreatment::Standard,
    ]);
    $payload = invoiceCreationPayload($firstMember, $product, [
        'recipients' => [$firstMember->getKey(), $secondMember->getKey()],
    ]);

    $response = $this->actingAs($user)
        ->postJson(route('clubs.invoice-creations.store', $club), $payload)
        ->assertCreated()
        ->assertJsonCount(2, 'data.invoices')
        ->assertJsonPath('data.invoices.0.number', 10001)
        ->assertJsonPath('data.invoices.1.number', 10002);

    expect($response->json('data.id'))->not->toBeNull()
        ->and($club->refresh()->invoice_sequence)->toBe(10002)
        ->and($club->invoices()->count())->toBe(2);

    $invoice = $club->invoices()->where('number', 10001)->with('lines')->sole();
    expect($invoice->gross_total_ore)->toBe(100000)
        ->and($invoice->net_total_ore)->toBe(80000)
        ->and($invoice->vat_total_ore)->toBe(20000)
        ->and($invoice->club_account_number)->toBe($club->account_number)
        ->and($invoice->lines->sole()->description)->toBe('Annual membership')
        ->and($invoice->lines->sole()->gross_unit_price_ore)->toBe(50000);

    $product->update(['name' => 'Changed product', 'description' => 'Changed description', 'gross_price_ore' => 1]);
    expect($invoice->refresh()->lines()->sole()->description)->toBe('Annual membership')
        ->and($invoice->lines()->sole()->gross_unit_price_ore)->toBe(50000);
});

test('repeating a submission token returns the original creation without duplicating invoices', function () {
    [$user, $club, $member] = invoiceClubContext();
    $product = Product::factory()->for($club)->create();
    $payload = invoiceCreationPayload($member, $product);

    $firstId = $this->actingAs($user)
        ->postJson(route('clubs.invoice-creations.store', $club), $payload)
        ->assertCreated()
        ->json('data.id');

    $this->actingAs($user)
        ->postJson(route('clubs.invoice-creations.store', $club), $payload)
        ->assertCreated()
        ->assertJsonPath('data.id', $firstId)
        ->assertJsonCount(1, 'data.invoices');

    expect(InvoiceCreation::query()->count())->toBe(1)
        ->and(Invoice::query()->count())->toBe(1)
        ->and($club->refresh()->invoice_sequence)->toBe(10001);
});

test('invoice creation validates recipients products dates organization number and account number', function () {
    [$user, $club, $member] = invoiceClubContext();
    $product = Product::factory()->for($club)->create();
    $inactiveProduct = Product::factory()->for($club)->create(['is_active' => false]);

    $this->actingAs($user)
        ->postJson(route('clubs.invoice-creations.store', $club), invoiceCreationPayload($member, $product, [
            'recipients' => [],
            'lines' => [],
            'due_date' => '2029-12-31',
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['recipients', 'lines', 'due_date']);

    $this->actingAs($user)
        ->postJson(route('clubs.invoice-creations.store', $club), invoiceCreationPayload($member, $product, [
            'recipients' => [$member->getKey(), $member->getKey()],
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('recipients.1');

    $this->actingAs($user)
        ->postJson(route('clubs.invoice-creations.store', $club), invoiceCreationPayload($member, $inactiveProduct))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('lines');

    $club->update(['organization_number' => null]);
    $this->actingAs($user)
        ->postJson(route('clubs.invoice-creations.store', $club), invoiceCreationPayload($member, $product))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('organization_number');

    $club->update(['organization_number' => '912345678', 'account_number' => null]);
    $this->actingAs($user)
        ->postJson(route('clubs.invoice-creations.store', $club), invoiceCreationPayload($member, $product))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('account_number');

    expect(Invoice::query()->count())->toBe(0);
});

test('the invoice form shows the organization number error after issue validation fails', function () {
    [$user, $club, $member] = invoiceClubContext(withOrganizationNumber: false);
    $product = Product::factory()->for($club)->create();

    $response = $this->actingAs($user)
        ->from(route('clubs.invoice-creations.create', $club))
        ->post(route('clubs.invoice-creations.store', $club), invoiceCreationPayload($member, $product));

    $this->followRedirects($response)
        ->assertSuccessful()
        ->assertSee(__('invoices.validation.organization_number'));
});

test('the invoice form shows the account number error after issue validation fails', function () {
    [$user, $club, $member] = invoiceClubContext(withAccountNumber: false);
    $product = Product::factory()->for($club)->create();

    $response = $this->actingAs($user)
        ->from(route('clubs.invoice-creations.create', $club))
        ->post(route('clubs.invoice-creations.store', $club), invoiceCreationPayload($member, $product));

    $this->followRedirects($response)
        ->assertSuccessful()
        ->assertSee(__('invoices.validation.account_number'));
});

test('invoice creation rejects cross club recipients and products', function () {
    [$user, $club, $member] = invoiceClubContext();
    $product = Product::factory()->for($club)->create();
    $otherClub = Club::factory()->create();
    $otherMember = Member::factory()->for($otherClub)->create();
    $otherProduct = Product::factory()->for($otherClub)->create();

    $this->actingAs($user)
        ->postJson(route('clubs.invoice-creations.store', $club), invoiceCreationPayload($member, $product, [
            'recipients' => [$otherMember->getKey()],
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('recipients');

    $this->actingAs($user)
        ->postJson(route('clubs.invoice-creations.store', $club), invoiceCreationPayload($member, $otherProduct))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('lines');
});

test('the creation page receives sorted members and active products', function () {
    [$user, $club] = invoiceClubContext();
    Member::factory()->for($club)->create(['name' => 'Aaron Member']);
    Product::factory()->for($club)->create(['name' => 'Zebra Product']);
    Product::factory()->for($club)->create(['name' => 'Alpha Product']);
    Product::factory()->for($club)->create(['name' => 'Hidden Product', 'is_active' => false]);

    $this->actingAs($user)
        ->get(route('clubs.invoice-creations.create', $club))
        ->assertSuccessful()
        ->assertViewHas('members', fn ($members): bool => $members->pluck('name')->all() === [
            'Aaron Member',
            'First Member',
            'Second Member',
        ])
        ->assertViewHas('products', fn ($products): bool => $products->pluck('name')->all() === [
            'Alpha Product',
            'Zebra Product',
        ]);
});

test('a creation result can only be opened through its own club', function () {
    [$user, $club] = invoiceClubContext();
    $otherClub = Club::factory()->create(['organization_number' => '987654321']);
    $creation = InvoiceCreation::factory()->for($otherClub)->create();

    $this->actingAs($user)
        ->getJson(route('clubs.invoice-creations.show', [$otherClub, $creation]))
        ->assertNotFound();

    $this->actingAs($user)
        ->getJson(route('clubs.invoice-creations.show', [$club, $creation]))
        ->assertNotFound();
});

test('issued invoices and their lines remain immutable', function () {
    [$user, $club, $member] = invoiceClubContext();
    $invoice = Invoice::factory()->for($club)->for($member)->create([
        'number' => 10001,
        'document_type' => InvoiceDocumentType::Invoice,
        'status' => InvoiceStatus::Issued,
    ]);
    $line = InvoiceLine::withoutEvents(fn (): InvoiceLine => $invoice->lines()->create([
        'description' => 'Membership',
        'quantity' => '1.00',
        'gross_unit_price_ore' => 50000,
        'vat_treatment' => VatTreatment::Standard,
        'net_amount_ore' => 40000,
        'vat_amount_ore' => 10000,
        'gross_amount_ore' => 50000,
    ]));

    expect(fn () => $invoice->update(['recipient_name' => 'Changed']))->toThrow(LogicException::class)
        ->and(fn () => $line->update(['description' => 'Changed']))->toThrow(LogicException::class)
        ->and(fn () => $line->delete())->toThrow(LogicException::class)
        ->and(fn () => $invoice->delete())->toThrow(LogicException::class);
});

test('an issued invoice can be credited only once using the shared sequence', function () {
    [$user, $club, $member] = invoiceClubContext();
    $invoice = Invoice::factory()->for($club)->for($member)->create([
        'number' => 10001,
        'document_type' => InvoiceDocumentType::Invoice,
        'status' => InvoiceStatus::Issued,
    ]);
    InvoiceLine::withoutEvents(fn (): InvoiceLine => $invoice->lines()->create([
        'description' => 'Membership',
        'quantity' => '1.00',
        'gross_unit_price_ore' => 50000,
        'vat_treatment' => VatTreatment::Standard,
        'net_amount_ore' => 40000,
        'vat_amount_ore' => 10000,
        'gross_amount_ore' => 50000,
    ]));
    $club->update(['invoice_sequence' => 10001]);

    $this->actingAs($user)
        ->postJson(route('clubs.invoices.credit', [$club, $invoice]))
        ->assertCreated()
        ->assertJsonPath('data.number', 10002);

    expect($invoice->refresh()->status)->toBe(InvoiceStatus::Credited)
        ->and($invoice->creditNote()->sole()->gross_total_ore)->toBe(-$invoice->gross_total_ore);

    $creditNote = $invoice->creditNote()->sole();

    $this->actingAs($user)
        ->get(route('clubs.invoices.show', [$club, $invoice]))
        ->assertSuccessful()
        ->assertSee(__('invoices.actions.view_credit_note', ['number' => $creditNote->number]))
        ->assertSee(route('clubs.invoices.show', [$club, $creditNote]), false)
        ->assertDontSee(__('invoices.actions.credit'));

    $this->actingAs($user)
        ->get(route('clubs.invoices.show', [$club, $creditNote]))
        ->assertSuccessful()
        ->assertSee(__('invoices.actions.view_invoice', ['number' => $invoice->number]))
        ->assertSee(route('clubs.invoices.show', [$club, $invoice]), false)
        ->assertDontSee(__('invoices.actions.credit'));

    $this->actingAs($user)
        ->postJson(route('clubs.invoices.credit', [$club, $invoice]))
        ->assertUnprocessable();
});

test('the invoice credit dialog submits with the credit route method', function () {
    [$user, $club, $member] = invoiceClubContext();
    $invoice = Invoice::factory()->for($club)->for($member)->create([
        'number' => 10001,
        'document_type' => InvoiceDocumentType::Invoice,
        'status' => InvoiceStatus::Issued,
    ]);

    $this->actingAs($user)
        ->get(route('clubs.invoices.show', [$club, $invoice]))
        ->assertSuccessful()
        ->assertSee(route('clubs.invoices.index', $club), false)
        ->assertSee(__('invoices.actions.overview'))
        ->assertSee('data-print-link', false)
        ->assertSee('method="POST"', false)
        ->assertSee(route('clubs.invoices.credit', [$club, $invoice]), false);
});

test('the invoice view shows when the email was last sent', function () {
    [$user, $club, $member] = invoiceClubContext();
    $invoice = Invoice::factory()->for($club)->for($member)->create([
        'email_sent_at' => '2026-09-20 14:35:00',
    ]);
    $formattedDate = $invoice->email_sent_at->locale(app()->getLocale())->translatedFormat('j. F Y H:i');

    $this->actingAs($user)
        ->get(route('clubs.invoices.show', [$club, $invoice]))
        ->assertSuccessful()
        ->assertSee(__('invoices.email_last_sent', ['date' => $formattedDate]));
});

test('an issued invoice can be marked paid once and displays the payment time', function () {
    [$user, $club, $member] = invoiceClubContext();
    $invoice = Invoice::factory()->for($club)->for($member)->create();

    $this->actingAs($user)
        ->post(route('clubs.invoices.mark-paid', [$club, $invoice]), [
            'paid_amount' => '100.50',
            'paid_date' => '2026-09-19',
        ])
        ->assertRedirect(route('clubs.invoices.show', [$club, $invoice]));

    $paidInvoice = $invoice->refresh();
    expect($paidInvoice->paid_at->toDateString())->toBe('2026-09-19')
        ->and($paidInvoice->paid_amount_ore)->toBe(10050);

    $this->actingAs($user)
        ->get(route('clubs.invoices.show', [$club, $invoice]))
        ->assertSuccessful()
        ->assertSee(__('invoices.paid_on', [
            'date' => $paidInvoice->paid_at->locale(app()->getLocale())->translatedFormat('j. F Y H:i'),
            'amount' => '100,50 kr',
        ]))
        ->assertSee(__('invoices.actions.edit_payment'))
        ->assertSee(__('invoices.actions.undo_paid'));

    $this->actingAs($user)
        ->post(route('clubs.invoices.mark-paid', [$club, $invoice]), [
            'paid_amount' => '99.99',
            'paid_date' => '2026-09-20',
        ])
        ->assertRedirect(route('clubs.invoices.show', [$club, $invoice]));

    expect($invoice->refresh()->paid_at->toDateString())->toBe('2026-09-20')
        ->and($invoice->paid_amount_ore)->toBe(9999);

    $this->actingAs($user)
        ->post(route('clubs.invoices.unmark-paid', [$club, $invoice]))
        ->assertRedirect(route('clubs.invoices.show', [$club, $invoice]));

    expect($invoice->refresh()->paid_at)->toBeNull()
        ->and($invoice->paid_amount_ore)->toBeNull();

    $this->actingAs($user)
        ->get(route('clubs.invoices.show', [$club, $invoice]))
        ->assertSuccessful()
        ->assertSee(__('invoices.actions.mark_paid'))
        ->assertDontSee(__('invoices.actions.undo_paid'));
});

test('the invoice overview contains no visible legacy terminology', function () {
    [$user, $club] = invoiceClubContext();

    $this->actingAs($user)
        ->get(route('clubs.invoices.index', $club), ['Accept-Language' => 'nb-NO'])
        ->assertSuccessful()
        ->assertSee('Opprett faktura')
        ->assertDontSee('batch', escape: false);
});

test('the entire creation rolls back when one recipient invoice fails', function () {
    [, $club, $firstMember, $secondMember] = invoiceClubContext();
    $product = Product::factory()->for($club)->create();
    $payload = invoiceCreationPayload($firstMember, $product, [
        'recipients' => [$firstMember->getKey(), $secondMember->getKey()],
    ]);
    $creatingCount = 0;

    Event::listen('eloquent.creating: '.Invoice::class, function () use (&$creatingCount): void {
        $creatingCount++;

        if ($creatingCount === 2) {
            throw new RuntimeException('Simulated second invoice failure.');
        }
    });

    try {
        expect(fn () => app(CreateInvoicesAction::class)->handle($club, $payload))
            ->toThrow(RuntimeException::class, 'Simulated second invoice failure.');
    } finally {
        Event::forget('eloquent.creating: '.Invoice::class);
    }

    expect(InvoiceCreation::query()->count())->toBe(0)
        ->and(Invoice::query()->count())->toBe(0)
        ->and(InvoiceLine::query()->count())->toBe(0)
        ->and($club->refresh()->invoice_sequence)->toBe(10000);
});

test('an empty draft can be saved without dates recipients or lines', function () {
    [$user, $club] = invoiceClubContext();
    $payload = [
        'intent' => 'draft',
        'submission_token' => (string) Str::uuid(),
    ];

    $response = $this->actingAs($user)
        ->postJson(route('clubs.invoice-creations.store', $club), $payload)
        ->assertCreated();

    expect($response->json('data.status'))->toBe(InvoiceCreationStatus::Draft->value)
        ->and($response->json('data.invoices'))->toBeNull();
    $this->assertDatabaseHas('invoice_creations', ['club_id' => $club->id, 'status' => 'draft']);
    expect($club->invoices()->count())->toBe(0);
});

test('a draft can be updated and reflects current product data', function () {
    [$user, $club, $member] = invoiceClubContext();
    $product = Product::factory()->for($club)->create(['description' => 'Original description']);
    $payload = invoiceCreationPayload($member, $product, [
        'intent' => 'draft',
        'invoice_date' => null,
        'due_date' => null,
    ]);

    $creationId = $this->actingAs($user)
        ->postJson(route('clubs.invoice-creations.store', $club), $payload)
        ->assertCreated()
        ->json('data.id');

    $this->actingAs($user)
        ->putJson(route('clubs.invoice-creations.update', [$club, $creationId]), [
            'intent' => 'draft',
            'submission_token' => $payload['submission_token'],
            'recipients' => [$member->id],
            'lines' => [['product_id' => $product->id, 'quantity' => '3.00']],
        ])
        ->assertOk();

    $product->update(['name' => 'Current product', 'description' => 'Current description', 'gross_price_ore' => 321]);

    $this->actingAs($user)
        ->get(route('clubs.invoice-creations.edit', [$club, $creationId]))
        ->assertSuccessful()
        ->assertSee('Current product')
        ->assertSee('Current description')
        ->assertSee('3.00');
});

test('drafts can be deleted but issued creations are locked', function () {
    [$user, $club, $member] = invoiceClubContext();
    $product = Product::factory()->for($club)->create();
    $draftPayload = invoiceCreationPayload($member, $product, ['intent' => 'draft']);
    $draftId = $this->actingAs($user)->postJson(route('clubs.invoice-creations.store', $club), $draftPayload)->json('data.id');

    $this->actingAs($user)
        ->deleteJson(route('clubs.invoice-creations.destroy', [$club, $draftId]))
        ->assertNoContent();

    $issuedId = $this->actingAs($user)->postJson(route('clubs.invoice-creations.store', $club), invoiceCreationPayload($member, $product))->json('data.id');
    $this->actingAs($user)
        ->putJson(route('clubs.invoice-creations.update', [$club, $issuedId]), invoiceCreationPayload($member, $product, ['intent' => 'draft']))
        ->assertForbidden();
    $this->actingAs($user)
        ->deleteJson(route('clubs.invoice-creations.destroy', [$club, $issuedId]))
        ->assertForbidden();
});

test('issuing a draft fails when its product becomes inactive', function () {
    [$user, $club, $member] = invoiceClubContext();
    $product = Product::factory()->for($club)->create();
    $payload = invoiceCreationPayload($member, $product, ['intent' => 'draft']);
    $creationId = $this->actingAs($user)->postJson(route('clubs.invoice-creations.store', $club), $payload)->json('data.id');
    $product->update(['is_active' => false]);

    $this->actingAs($user)
        ->putJson(route('clubs.invoice-creations.update', [$club, $creationId]), array_replace($payload, ['intent' => 'issue']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('lines');

    expect($club->invoices()->count())->toBe(0);
});

test('a product used by a draft cannot be deleted', function () {
    [$user, $club, $member] = invoiceClubContext();
    $product = Product::factory()->for($club)->create();
    $this->actingAs($user)->postJson(route('clubs.invoice-creations.store', $club), invoiceCreationPayload($member, $product, ['intent' => 'draft']))->assertCreated();

    $this->actingAs($user)
        ->deleteJson(route('clubs.products.destroy', [$club, $product]))
        ->assertForbidden();
});
