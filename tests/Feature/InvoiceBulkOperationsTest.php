<?php

use App\Jobs\BuildInvoiceExportArchive;
use App\Jobs\GenerateInvoiceExportPdf;
use App\Jobs\SendInvoiceEmail;
use App\Mail\InvoiceExportReadyMail;
use App\Models\Club;
use App\Models\Invoice;
use App\Models\InvoiceExport;
use App\Models\Member;
use App\Models\User;
use App\Services\InvoicePdfService;
use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

function bulkInvoiceContext(): array
{
    $user = User::factory()->create();
    $club = Club::factory()->create();
    $member = Member::factory()->for($club)->for($user)->create();

    return [$user, $club, $member];
}

test('bulk sending resends selected invoices and skips those without an email address', function (): void {
    Queue::fake();
    [$user, $club, $member] = bulkInvoiceContext();
    $sendableInvoice = Invoice::factory()->for($club)->for($member)->create([
        'email_sent_at' => now(),
        'recipient_email' => 'recipient@example.com',
    ]);
    $skippedInvoice = Invoice::factory()->for($club)->for($member)->create(['recipient_email' => null]);

    $this->actingAs($user)
        ->postJson(route('clubs.invoices.bulk-send', $club), ['invoice_ids' => [$sendableInvoice->getKey(), $skippedInvoice->getKey()]])
        ->assertSuccessful()
        ->assertJsonPath('queued', 1)
        ->assertJsonPath('skipped', 1);

    Queue::assertPushed(SendInvoiceEmail::class, fn (SendInvoiceEmail $job): bool => $job->invoice->is($sendableInvoice) && $job->resend);
});

test('the invoice index renders direct bulk action forms', function (): void {
    [$user, $club, $member] = bulkInvoiceContext();
    Invoice::factory()->for($club)->for($member)->create();

    $this->actingAs($user)
        ->get(route('clubs.invoices.index', $club))
        ->assertSuccessful()
        ->assertSee('id="invoice-bulk-form"', false)
        ->assertSee('form="invoice-bulk-form"', false)
        ->assertSee('formaction="'.route('clubs.invoices.bulk-send', $club).'"', false)
        ->assertSee('formaction="'.route('clubs.invoices.exports.store', $club).'"', false);
});

test('batch invoice operations reject empty duplicate and cross-club selections', function (): void {
    [$user, $club, $member] = bulkInvoiceContext();
    $invoice = Invoice::factory()->for($club)->for($member)->create();
    $otherClub = Club::factory()->create();
    $otherInvoice = Invoice::factory()->for($otherClub)->create();

    $this->actingAs($user)->postJson(route('clubs.invoices.bulk-send', $club), ['invoice_ids' => []])
        ->assertUnprocessable();
    $this->actingAs($user)->postJson(route('clubs.invoices.bulk-send', $club), ['invoice_ids' => [$invoice->getKey(), $invoice->getKey()]])
        ->assertUnprocessable();
    $this->actingAs($user)->postJson(route('clubs.invoices.exports.store', $club), ['invoice_ids' => [$otherInvoice->getKey()]])
        ->assertUnprocessable();
});

test('an invoice export queues one PDF job for every selected invoice', function (): void {
    Bus::fake();
    [$user, $club, $member] = bulkInvoiceContext();
    $firstInvoice = Invoice::factory()->for($club)->for($member)->create();
    $secondInvoice = Invoice::factory()->for($club)->for($member)->create();

    $this->actingAs($user)
        ->postJson(route('clubs.invoices.exports.store', $club), ['invoice_ids' => [$firstInvoice->getKey(), $secondInvoice->getKey()]])
        ->assertAccepted();

    expect($export = InvoiceExport::query()->sole())->status->toBe('processing')
        ->and($export->invoice_ids)->toEqualCanonicalizing([$firstInvoice->getKey(), $secondInvoice->getKey()]);

    Bus::assertBatched(function (PendingBatch $batch) use ($export): bool {
        return count($batch->jobs) === 2
            && collect($batch->jobs)->every(fn (GenerateInvoiceExportPdf $job): bool => $job->invoiceExportId === $export->getKey());
    });
});

test('anyone with the unique export URL can download a completed export', function (): void {
    Storage::fake('local');
    [$owner, $club] = bulkInvoiceContext();
    $export = InvoiceExport::factory()->for($club)->for($owner)->create([
        'status' => 'completed',
        'archive_path' => 'invoice-exports/1/invoices.zip',
    ]);
    Storage::disk('local')->put($export->archive_path, 'zip contents');
    $url = route('invoice-exports.download', ['invoiceExport' => $export]);

    $this->get($url)->assertSuccessful()->assertDownload('invoices.zip');

    $otherUser = User::factory()->create();
    $this->actingAs($otherUser)->get($url)->assertSuccessful()->assertDownload('invoices.zip');
});

test('a completed PDF batch builds a ZIP archive and emails its owner', function (): void {
    Storage::fake('local');
    Mail::fake();
    [$user, $club, $member] = bulkInvoiceContext();
    $invoice = Invoice::factory()->for($club)->for($member)->create(['number' => 10101]);
    $export = InvoiceExport::factory()->for($club)->for($user)->create(['invoice_ids' => [$invoice->getKey()]]);
    $pdfService = app(InvoicePdfService::class);
    Storage::disk('local')->put($pdfService->path($invoice), 'PDF content');

    (new BuildInvoiceExportArchive($export->getKey()))->handle($pdfService);

    expect($export->refresh()->status)->toBe('completed');
    Storage::disk('local')->assertExists($export->archive_path);
    Mail::assertSent(InvoiceExportReadyMail::class, fn (InvoiceExportReadyMail $mail): bool => $mail->hasTo($user->email));
});

test('expired invoice exports are removed with their archives', function (): void {
    Storage::fake('local');
    [$user, $club] = bulkInvoiceContext();
    $export = InvoiceExport::factory()->for($club)->for($user)->create([
        'archive_path' => 'invoice-exports/expired/invoices.zip',
        'expires_at' => now()->subMinute(),
    ]);
    Storage::disk('local')->put($export->archive_path, 'zip contents');

    $this->artisan('app:purge-expired-invoice-exports')->assertSuccessful();

    expect(InvoiceExport::query()->find($export->getKey()))->toBeNull();
    Storage::disk('local')->assertMissing('invoice-exports/expired/invoices.zip');
});

test('the invoice export ready email contains a unique download link', function (): void {
    Mail::fake();
    [$user, $club] = bulkInvoiceContext();
    $export = InvoiceExport::factory()->for($club)->for($user)->create();
    $url = route('invoice-exports.download', ['invoiceExport' => $export]);

    Mail::to($user->email)->send(new InvoiceExportReadyMail($export, $url));

    Mail::assertSent(InvoiceExportReadyMail::class, fn (InvoiceExportReadyMail $mail): bool => $mail->hasTo($user->email) && $mail->downloadUrl === $url);
});
