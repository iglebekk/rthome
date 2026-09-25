<?php

namespace App\Models;

use Database\Factories\ClubFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable(['name', 'organization_number', 'invoice_name', 'account_number', 'locale', 'invoice_sequence', 'public_events_token',
    'public_events_enabled'])]
class Club extends Model
{
    /** @use HasFactory<ClubFactory> */
    use HasFactory;

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(ClubInvitation::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function invoiceCreations(): HasMany
    {
        return $this->hasMany(InvoiceCreation::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function invoiceExports(): HasMany
    {
        return $this->hasMany(InvoiceExport::class);
    }

    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            Member::class,
            'club_id',
            'id',
            'id',
            'user_id',
        );
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'public_events_enabled' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Club $club): void {
            if ($club->invoices()->exists()) {
                throw new \LogicException('A club with invoices cannot be deleted.');
            }

            $club->links()->eachById(
                function (Link $link): void {
                    $link->delete();
                },
            );

            $club->invitations()->eachById(
                function (ClubInvitation $invitation): void {
                    $invitation->delete();
                },
            );

            $club->events()->eachById(
                function (Event $event): void {
                    $event->delete();
                },
            );
        });
    }
}
