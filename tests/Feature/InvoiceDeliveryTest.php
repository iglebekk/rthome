<?php

use App\Jobs\SendInvoiceEmail;
use App\Mail\InvoiceMailable;
use App\Models\Club;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\User;
use App\Services\InvoicePdfService;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\Facades\Pdf;

uses(LazilyRefreshDatabase::class);

it('renders an invoice print view without invoking a PDF renderer', function (): void {
    app()->setLocale('nb');

    $user = User::factory()->create();
    $club = Club::factory()->create();
    $member = Member::factory()->for($club)->for($user)->create();
    $invoice = Invoice::factory()->for($club)->for($member)->create(['number' => 10001]);

    $this->actingAs($user)
        ->get(route('clubs.invoices.download', [$club, $invoice]))
        ->assertSuccessful()
        ->assertHeader('content-type', 'text/html; charset=UTF-8')
        ->assertSee('data-print-on-load', false)
        ->assertSee('data-print-return-url=', false)
        ->assertSee('<title>'.__('invoices.document_filename', ['number' => 10001]).'</title>', false)
        ->assertSee('10001');
});

it('uses the invoice number in generated PDF filenames', function (): void {
    $invoice = (new Invoice)->forceFill(['number' => 100174]);

    expect(app(InvoicePdfService::class)->filename($invoice))->toBe('faktura 100174.pdf');
});

it('renders an immutable invoice PDF to private storage', function (): void {
    Storage::fake('local');
    Pdf::fake();

    $invoice = (new Invoice)->forceFill([
        'id' => 42,
        'club_id' => 7,
        'number' => 10001,
        'document_type' => 'invoice',
        'invoice_date' => '2026-09-16',
        'due_date' => '2026-09-30',
        'club_name' => 'Example Club',
        'club_organization_number' => '123456789',
        'club_account_number' => '12345678903',
        'recipient_name' => 'Ada Lovelace',
        'recipient_company_name' => null,
        'recipient_organization_number' => null,
        'recipient_address' => null,
        'recipient_postal_code' => null,
        'recipient_city' => null,
        'net_total_ore' => 80000,
        'vat_total_ore' => 20000,
        'gross_total_ore' => 100000,
    ]);
    $invoice->setRelation('lines', new Collection);

    $path = app(InvoicePdfService::class)->generate($invoice);

    expect($path)->toBe('invoices/7/10001.pdf');

    Pdf::assertSaved(fn ($pdf, string $savedPath): bool => str_ends_with($savedPath, $path));
    Pdf::assertViewIs('pdfs.invoice');
    Pdf::assertViewHas('document');
});

it('renders an invoice PDF without a browser runtime', function (): void {
    Storage::fake('local');

    $invoice = (new Invoice)->forceFill([
        'id' => 42,
        'club_id' => 7,
        'number' => 10001,
        'document_type' => 'invoice',
        'invoice_date' => '2026-09-16',
        'due_date' => '2026-09-30',
        'club_name' => 'Example Club',
        'club_organization_number' => '123456789',
        'club_account_number' => '12345678903',
        'recipient_name' => 'Ada Lovelace',
        'recipient_company_name' => null,
        'recipient_organization_number' => null,
        'recipient_address' => null,
        'recipient_postal_code' => null,
        'recipient_city' => null,
        'net_total_ore' => 80000,
        'vat_total_ore' => 20000,
        'gross_total_ore' => 100000,
    ]);
    $invoice->setRelation('lines', new Collection);

    $path = app(InvoicePdfService::class)->generate($invoice);

    expect(Storage::disk('local')->get($path))->toStartWith('%PDF');
});

it('includes the club account number in the pdf document data', function (): void {
    $invoice = (new Invoice)->forceFill([
        'number' => 10001,
        'document_type' => 'invoice',
        'invoice_date' => '2026-09-16',
        'due_date' => '2026-09-30',
        'club_name' => 'Example Club',
        'club_organization_number' => '123456789',
        'club_account_number' => '12345678903',
        'recipient_name' => 'Ada Lovelace',
        'net_total_ore' => 80000,
        'vat_total_ore' => 20000,
        'gross_total_ore' => 100000,
    ]);
    $invoice->setRelation('lines', new Collection);

    expect(app(InvoicePdfService::class)->document($invoice)['seller_account_number'])->toBe('12345678903');
});

it('falls back to the club current account number when an invoice has none of its own', function (): void {
    $club = Club::factory()->create(['account_number' => '12345678903']);
    $invoice = (new Invoice)->forceFill([
        'number' => 10001,
        'document_type' => 'invoice',
        'invoice_date' => '2026-09-16',
        'due_date' => '2026-09-30',
        'club_name' => 'Example Club',
        'club_organization_number' => '123456789',
        'club_account_number' => null,
        'recipient_name' => 'Ada Lovelace',
        'net_total_ore' => 80000,
        'vat_total_ore' => 20000,
        'gross_total_ore' => 100000,
    ]);
    $invoice->setRelation('lines', new Collection);
    $invoice->setRelation('club', $club);

    expect(app(InvoicePdfService::class)->document($invoice)['seller_account_number'])->toBe('12345678903');
});

it('prefers its own snapshotted account number over the club current one', function (): void {
    $club = Club::factory()->create(['account_number' => '22222222222']);
    $invoice = (new Invoice)->forceFill([
        'number' => 10001,
        'document_type' => 'invoice',
        'invoice_date' => '2026-09-16',
        'due_date' => '2026-09-30',
        'club_name' => 'Example Club',
        'club_organization_number' => '123456789',
        'club_account_number' => '11111111111',
        'recipient_name' => 'Ada Lovelace',
        'net_total_ore' => 80000,
        'vat_total_ore' => 20000,
        'gross_total_ore' => 100000,
    ]);
    $invoice->setRelation('lines', new Collection);
    $invoice->setRelation('club', $club);

    expect(app(InvoicePdfService::class)->document($invoice)['seller_account_number'])->toBe('11111111111');
});

it('shows the club current account number on the invoice page when an older invoice has none of its own', function (): void {
    $user = User::factory()->create();
    $club = Club::factory()->create(['account_number' => '12345678903']);
    $member = Member::factory()->for($club)->for($user)->create();
    $invoice = Invoice::factory()->for($club)->for($member)->create(['club_account_number' => null]);

    $this->actingAs($user)
        ->get(route('clubs.invoices.show', [$club, $invoice]))
        ->assertSuccessful()
        ->assertSee('12345678903');
});

it('does not send an invoice email a second time after it is marked sent', function (): void {
    Mail::fake();

    $invoice = (new Invoice)->forceFill([
        'email_sent_at' => now(),
        'recipient_email' => 'ada@example.com',
    ]);

    $job = new SendInvoiceEmail($invoice);
    $job->handle(app(InvoicePdfService::class));

    Mail::assertNothingSent();
});

it('requires an email address before sending an invoice', function (): void {
    $invoice = (new Invoice)->forceFill(['recipient_email' => null]);

    expect(fn (): mixed => (new SendInvoiceEmail($invoice))->handle(app(InvoicePdfService::class)))
        ->toThrow(InvalidArgumentException::class);
});

it('stores the translated sent message in the flash session', function (): void {
    Queue::fake();

    $user = User::factory()->create();
    $club = Club::factory()->create();
    $member = Member::factory()->for($club)->for($user)->create();
    $invoice = Invoice::factory()->for($club)->for($member)->create([
        'recipient_email' => 'recipient@example.com',
    ]);

    $this->actingAs($user)
        ->post(route('clubs.invoices.send', [$club, $invoice]))
        ->assertRedirect(route('clubs.invoices.show', [$club, $invoice]))
        ->assertSessionHas('status', __('invoices.messages.sent'));

    Queue::assertPushed(SendInvoiceEmail::class);
});

it('queues invoice email work after the transaction commits', function (): void {
    app()->setLocale('en');

    $invoice = (new Invoice)->forceFill(['number' => 10001]);
    $mailable = new InvoiceMailable($invoice, 'invoices/7/10001.pdf');

    expect(new SendInvoiceEmail($invoice))
        ->toBeInstanceOf(ShouldQueueAfterCommit::class)
        ->and($mailable->envelope()->subject)
        ->toBe('Invoice 10001');
});

it('renders the invoice mail subject in the current app locale', function (): void {
    $invoice = (new Invoice)->forceFill(['number' => 10001]);

    app()->setLocale('nb');
    expect((new InvoiceMailable($invoice, 'invoices/7/10001.pdf'))->envelope()->subject)->toBe('Faktura 10001');

    app()->setLocale('en');
    expect((new InvoiceMailable($invoice, 'invoices/7/10001.pdf'))->envelope()->subject)->toBe('Invoice 10001');
});

it('renders locale-dependent pdf strings using the current app locale', function (): void {
    $invoice = (new Invoice)->forceFill([
        'number' => 10001,
        'document_type' => 'invoice',
        'invoice_date' => '2026-09-16',
        'due_date' => '2026-09-30',
        'club_name' => 'Example Club',
        'club_organization_number' => '123456789',
        'club_account_number' => '12345678903',
        'recipient_name' => 'Ada Lovelace',
        'net_total_ore' => 80000,
        'vat_total_ore' => 20000,
        'gross_total_ore' => 100000,
    ]);
    $invoice->setRelation('lines', new Collection);

    app()->setLocale('nb');
    expect(app(InvoicePdfService::class)->document($invoice)['document_type'])->toBe('Faktura');

    app()->setLocale('en');
    expect(app(InvoicePdfService::class)->document($invoice)['document_type'])->toBe('Invoice');
});

it('falls back to the club current locale when an invoice has none of its own', function (): void {
    $club = Club::factory()->create(['locale' => 'nb']);
    $invoice = (new Invoice)->forceFill(['club_locale' => null]);
    $invoice->setRelation('club', $club);

    $locale = $invoice->club_locale ?? $invoice->club?->locale ?? config('app.fallback_locale');

    expect($locale)->toBe('nb');
});

it('sends the invoice using the club locale and restores the previous locale afterward', function (): void {
    Mail::fake();
    Storage::fake('local');
    Pdf::fake();
    app()->setLocale('en');

    $user = User::factory()->create();
    $club = Club::factory()->create(['locale' => 'nb']);
    $member = Member::factory()->for($club)->for($user)->create();
    $invoice = Invoice::factory()->for($club)->for($member)->create([
        'club_locale' => 'nb',
        'recipient_email' => 'ada@example.com',
    ]);

    (new SendInvoiceEmail($invoice))->handle(app(InvoicePdfService::class));

    Pdf::assertSee('Faktura');
    Mail::assertSent(InvoiceMailable::class, fn (InvoiceMailable $mail): bool => $mail->locale === 'nb');
    expect(app()->getLocale())->toBe('en');
});
