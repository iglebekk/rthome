<?php

namespace App\Jobs;

use App\Mail\InvoiceMailable;
use App\Models\Invoice;
use App\Services\InvoicePdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\PendingMail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use Throwable;

class SendInvoiceEmail implements ShouldQueueAfterCommit
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public Invoice $invoice,
        public bool $resend = false,
    ) {}

    public function handle(InvoicePdfService $pdfService): void
    {
        if ($this->invoice->email_sent_at !== null && ! $this->resend) {
            return;
        }

        if (blank($this->invoice->recipient_email)) {
            throw new InvalidArgumentException('An invoice recipient email address is required.');
        }

        $this->recordAttempt();

        try {
            $path = $pdfService->generate($this->invoice);

            /** @var PendingMail $mail */
            $mail = Mail::to($this->invoice->recipient_email);
            $mail->send(new InvoiceMailable($this->invoice, $path));

            $this->invoice->forceFill([
                'email_sent_at' => now(),
                'email_last_error' => null,
            ])->saveQuietly();
        } catch (Throwable $exception) {
            $this->invoice->forceFill([
                'email_last_error' => $exception->getMessage(),
            ])->saveQuietly();

            throw $exception;
        }
    }

    private function recordAttempt(): void
    {
        $this->invoice->forceFill([
            'email_send_attempts' => ((int) $this->invoice->email_send_attempts) + 1,
        ])->saveQuietly();
    }
}
