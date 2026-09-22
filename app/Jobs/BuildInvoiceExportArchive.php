<?php

namespace App\Jobs;

use App\Mail\InvoiceExportReadyMail;
use App\Models\InvoiceExport;
use App\Services\InvoicePdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use RuntimeException;
use Throwable;
use ZipArchive;

class BuildInvoiceExportArchive implements ShouldQueueAfterCommit
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $invoiceExportId) {}

    public function handle(InvoicePdfService $pdfService): void
    {
        $export = InvoiceExport::query()->with('user')->findOrFail($this->invoiceExportId);
        $disk = Storage::disk('local');
        $archivePath = "invoice-exports/{$export->getKey()}/invoices.zip";

        try {
            $disk->makeDirectory(dirname($archivePath));
            $archive = new ZipArchive;
            $opened = $archive->open($disk->path($archivePath), ZipArchive::CREATE | ZipArchive::OVERWRITE);

            if ($opened !== true) {
                throw new RuntimeException('Unable to create invoice export archive.');
            }

            $invoices = $export->club->invoices()
                ->whereKey($export->invoice_ids)
                ->orderBy('number')
                ->get();

            foreach ($invoices as $invoice) {
                $path = $pdfService->path($invoice);

                if (! $disk->exists($path)) {
                    throw new RuntimeException('An invoice PDF is missing from the export.');
                }

                $archive->addFile($disk->path($path), $pdfService->filename($invoice));
            }

            $archive->close();

            $export->forceFill([
                'status' => 'completed',
                'archive_path' => $archivePath,
            ])->save();

            $url = URL::route('invoice-exports.download', ['invoiceExport' => $export]);
            Mail::to($export->user->email)->send(new InvoiceExportReadyMail($export, $url));
        } catch (Throwable $exception) {
            $export->forceFill(['status' => 'failed'])->save();

            throw $exception;
        }
    }
}
