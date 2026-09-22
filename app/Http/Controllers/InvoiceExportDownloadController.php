<?php

namespace App\Http\Controllers;

use App\Models\InvoiceExport;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceExportDownloadController extends Controller
{
    public function __invoke(InvoiceExport $invoiceExport): StreamedResponse
    {
        abort_if($invoiceExport->expires_at->isPast(), 410);
        abort_unless($invoiceExport->status === 'completed' && $invoiceExport->archive_path !== null, 404);
        abort_unless(Storage::disk('local')->exists($invoiceExport->archive_path), 404);

        return Storage::disk('local')->download($invoiceExport->archive_path, 'invoices.zip');
    }
}
