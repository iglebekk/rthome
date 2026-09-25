<?php

namespace App\Models;

use Database\Factories\InvoiceExportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['club_id', 'user_id', 'invoice_ids', 'batch_id', 'status', 'archive_path', 'expires_at'])]
class InvoiceExport extends Model
{
    /** @use HasFactory<InvoiceExportFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (self $export): void {
            $export->download_token ??= Str::random(64);
        });
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'invoice_ids' => 'array',
            'expires_at' => 'datetime',
        ];
    }
}
