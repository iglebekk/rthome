<?php

namespace App\Models;

use App\Enums\VatTreatment;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['club_id', 'name', 'description', 'gross_price_ore', 'vat_treatment', 'is_active'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function invoiceCreationLines(): HasMany
    {
        return $this->hasMany(InvoiceCreationLine::class);
    }

    protected function casts(): array
    {
        return [
            'gross_price_ore' => 'integer',
            'vat_treatment' => VatTreatment::class,
            'is_active' => 'boolean',
        ];
    }
}
