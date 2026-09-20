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
    $invoice = (new Invoice)->forceFill(['number' => 10001]);
    $mailable = new InvoiceMailable($invoice, 'invoices/7/10001.pdf');

    expect(new SendInvoiceEmail($invoice))
        ->toBeInstanceOf(ShouldQueueAfterCommit::class)
        ->and($mailable->envelope()->subject)
        ->toBe('Invoice 10001');
});
