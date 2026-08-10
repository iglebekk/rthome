<?php

namespace App\Models;

use Database\Factories\ClubFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable(['name'])]
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

    protected static function booted(): void
    {
        static::deleting(function (Club $club): void {
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
