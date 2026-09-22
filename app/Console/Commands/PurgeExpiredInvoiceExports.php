<?php

namespace App\Console\Commands;

use App\Models\InvoiceExport;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('app:purge-expired-invoice-exports')]
#[Description('Delete expired invoice export archives')]
class PurgeExpiredInvoiceExports extends Command
{
    public function handle(): int
    {
        InvoiceExport::query()
            ->where('expires_at', '<=', now())
            ->eachById(function (InvoiceExport $export): void {
                if ($export->archive_path !== null) {
                    Storage::disk('local')->delete($export->archive_path);
                }

                $export->delete();
            });

        return self::SUCCESS;
    }
}
