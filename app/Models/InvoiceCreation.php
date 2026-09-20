<?php

namespace App\Models;

use App\Enums\InvoiceCreationStatus;
use Database\Factories\InvoiceCreationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['club_id', 'submission_token', 'status', 'invoice_date', 'due_date', 'issued_at'])]
class InvoiceCreation extends Model
{
    /** @use HasFactory<InvoiceCreationFactory> */
    use HasFactory;

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(Member::class)->withTimestamps();
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceCreationLine::class)->orderBy('position');
    }

    protected function casts(): array
    {
        return [
            'status' => InvoiceCreationStatus::class,
            'invoice_date' => 'date',
            'due_date' => 'date',
            'issued_at' => 'datetime',
        ];
    }
}
