<?php

namespace App\Jobs;

use App\Models\InvoiceExport;
use App\Services\InvoicePdfService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

class GenerateInvoiceExportPdf implements ShouldQueueAfterCommit
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $invoiceExportId,
        public int $invoiceId,
    ) {}

    public function handle(InvoicePdfService $pdfService): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $export = InvoiceExport::query()->findOrFail($this->invoiceExportId);
        $invoice = $export->club->invoices()->findOrFail($this->invoiceId);
        $invoice->setRelation('club', $export->club);

        $locale = $invoice->club_locale ?? $invoice->club?->locale ?? config('app.fallback_locale');
        $previousLocale = App::getLocale();

        try {
            App::setLocale($locale);
            Carbon::setLocale($locale);

            $pdfService->generate($invoice);
        } finally {
            App::setLocale($previousLocale);
            Carbon::setLocale($previousLocale);
        }
    }
}
