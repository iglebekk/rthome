<?php

namespace App\Models;

use Database\Factories\InvoiceCreationLineFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['invoice_creation_id', 'product_id', 'quantity', 'position'])]
class InvoiceCreationLine extends Model
{
    /** @use HasFactory<InvoiceCreationLineFactory> */
    use HasFactory;

    public function creation(): BelongsTo
    {
        return $this->belongsTo(InvoiceCreation::class, 'invoice_creation_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'position' => 'integer',
        ];
    }
}
