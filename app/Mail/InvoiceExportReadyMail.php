<?php

namespace App\Mail;

use App\Models\InvoiceExport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceExportReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public InvoiceExport $invoiceExport,
        public string $downloadUrl,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('invoices.export.mail.subject'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoices.export-ready',
        );
    }
}
