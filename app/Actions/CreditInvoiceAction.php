<?php

namespace App\Actions;

use App\Enums\InvoiceDocumentType;
use App\Enums\InvoiceStatus;
use App\Models\Club;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreditInvoiceAction
{
    public function handle(Invoice $invoice): Invoice
    {
        return DB::transaction(function () use ($invoice): Invoice {
            $club = $invoice->club()->firstOrFail();
            $lockedClub = Club::query()->whereKey($club->getKey())->lockForUpdate()->firstOrFail();
            $lockedInvoice = $lockedClub->invoices()->whereKey($invoice->getKey())->lockForUpdate()->with('lines')->firstOrFail();
            if ($lockedInvoice->document_type !== InvoiceDocumentType::Invoice || $lockedInvoice->status !== InvoiceStatus::Issued) {
                throw ValidationException::withMessages(['invoice' => __('Only an issued invoice can be credited once.')]);
            }
            if ($lockedInvoice->creditNote()->exists()) {
                throw ValidationException::withMessages(['invoice' => __('This invoice has already been credited.')]);
            }
            $number = (int) $lockedClub->invoice_sequence + 1;
            $creditNote = $lockedClub->invoices()->create([
                'invoice_creation_id' => $lockedInvoice->invoice_creation_id, 'member_id' => $lockedInvoice->member_id,
                'credited_invoice_id' => $lockedInvoice->getKey(), 'document_type' => InvoiceDocumentType::CreditNote,
                'status' => InvoiceStatus::Issued, 'number' => $number, 'invoice_date' => now()->toDateString(),
                'due_date' => now()->toDateString(), 'issued_at' => now(),
                'club_organization_number' => $lockedInvoice->club_organization_number, 'recipient_name' => $lockedInvoice->recipient_name,
                'recipient_company_name' => $lockedInvoice->recipient_company_name, 'recipient_organization_number' => $lockedInvoice->recipient_organization_number,
                'recipient_address' => $lockedInvoice->recipient_address, 'recipient_postal_code' => $lockedInvoice->recipient_postal_code,
                'recipient_city' => $lockedInvoice->recipient_city, 'recipient_email' => $lockedInvoice->recipient_email,
                'net_total_ore' => -$lockedInvoice->net_total_ore, 'vat_total_ore' => -$lockedInvoice->vat_total_ore,
                'gross_total_ore' => -$lockedInvoice->gross_total_ore,
            ]);
            InvoiceLine::withoutEvents(function () use ($creditNote, $lockedInvoice): void {
                foreach ($lockedInvoice->lines as $line) {
                    $creditNote->lines()->create([
                        'product_id' => $line->product_id, 'position' => $line->position, 'description' => $line->description,
                        'quantity' => $line->quantity, 'gross_unit_price_ore' => $line->gross_unit_price_ore,
                        'vat_treatment' => $line->vat_treatment, 'net_amount_ore' => -$line->net_amount_ore,
                        'vat_amount_ore' => -$line->vat_amount_ore, 'gross_amount_ore' => -$line->gross_amount_ore,
                    ]);
                }
            });
            $lockedInvoice->forceFill(['status' => InvoiceStatus::Credited])->saveQuietly();
            $lockedClub->update(['invoice_sequence' => $number]);

            return $creditNote->load('lines');
        });
    }
}
