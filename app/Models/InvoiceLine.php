<?php

namespace App\Models;

use App\Enums\VatTreatment;
use Database\Factories\InvoiceLineFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['invoice_id', 'product_id', 'position', 'description', 'quantity', 'gross_unit_price_ore', 'vat_treatment', 'net_amount_ore', 'vat_amount_ore', 'gross_amount_ore'])]
class InvoiceLine extends Model
{
    /** @use HasFactory<InvoiceLineFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (InvoiceLine $line): void {
            if (Invoice::query()->whereKey($line->invoice_id)->exists()) {
                throw new \LogicException('Invoice lines cannot be added to an issued invoice.');
            }
        });

        static::updating(function (): void {
            throw new \LogicException('Invoice lines cannot be changed.');
        });

        static::deleting(function (): void {
            throw new \LogicException('Invoice lines cannot be deleted.');
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'gross_unit_price_ore' => 'integer',
            'vat_treatment' => VatTreatment::class,
            'net_amount_ore' => 'integer',
            'vat_amount_ore' => 'integer',
            'gross_amount_ore' => 'integer',
        ];
    }
}
