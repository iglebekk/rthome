<?php

namespace App\Actions;

use App\Models\Club;
use App\Models\ClubInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AcceptClubInvitationAction
{
    public function find(string $token): ?ClubInvitation
    {
        $invitation = ClubInvitation::query()
            ->with('club')
            ->whereIn('token_hash', [$token, hash('sha256', $token)])
            ->first();

        return $invitation?->isUsable() ? $invitation : null;
    }

    public function handle(User $user, string $token): ?Club
    {
        return DB::transaction(function () use ($user, $token): ?Club {
            $invitation = ClubInvitation::query()
                ->with('club')
                ->lockForUpdate()
                ->whereIn('token_hash', [$token, hash('sha256', $token)])
                ->first();

            if ($invitation === null || ! $invitation->isUsable()) {
                return null;
            }

            if (! $invitation->club->members()->where('user_id', $user->getKey())->exists()) {
                $invitation->club->members()->create([
                    'user_id' => $user->getKey(),
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => null,
                ]);
            }

            return $invitation->club;
        });
    }

    public function reserve(User $user, string $token): void
    {
        $invitation = $this->find($token);

        if ($invitation === null || $invitation->club->members()->where('email', $user->email)->exists()) {
            return;
        }

        $invitation->club->members()->create([
            'name' => $user->name,
            'email' => $user->email,
            'phone' => null,
        ]);
    }
}
