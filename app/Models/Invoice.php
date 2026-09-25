<?php

namespace App\Models;

use App\Enums\InvoiceDocumentType;
use App\Enums\InvoiceStatus;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['club_id', 'invoice_creation_id', 'member_id', 'credited_invoice_id', 'document_type', 'status', 'number', 'invoice_date', 'due_date', 'issued_at', 'paid_at', 'paid_amount_ore', 'club_organization_number', 'club_account_number', 'club_locale', 'recipient_name', 'recipient_company_name', 'recipient_organization_number', 'recipient_address', 'recipient_postal_code', 'recipient_city', 'recipient_email', 'net_total_ore', 'vat_total_ore', 'gross_total_ore'])]
class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::updating(function (Invoice $invoice): void {
            $dirtyAttributes = array_diff(
                array_keys($invoice->getDirty()),
                ['email_sent_at', 'email_send_attempts', 'email_last_error', 'updated_at'],
            );

            if ($dirtyAttributes !== []) {
                throw new \LogicException('Issued invoices cannot be changed.');
            }
        });

        static::deleting(function (): void {
            throw new \LogicException('Issued invoices cannot be deleted.');
        });
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function creation(): BelongsTo
    {
        return $this->belongsTo(InvoiceCreation::class, 'invoice_creation_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function creditedInvoice(): BelongsTo
    {
        return $this->belongsTo(self::class, 'credited_invoice_id');
    }

    public function creditNote(): HasMany
    {
        return $this->hasMany(self::class, 'credited_invoice_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class)->orderBy('position');
    }

    protected function casts(): array
    {
        return [
            'document_type' => InvoiceDocumentType::class,
            'status' => InvoiceStatus::class,
            'number' => 'integer',
            'invoice_date' => 'date',
            'due_date' => 'date',
            'issued_at' => 'datetime',
            'paid_at' => 'datetime',
            'paid_amount_ore' => 'integer',
            'net_total_ore' => 'integer',
            'vat_total_ore' => 'integer',
            'gross_total_ore' => 'integer',
            'email_sent_at' => 'datetime',
            'email_send_attempts' => 'integer',
        ];
    }
}
