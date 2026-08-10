<?php

namespace App\Policies;

use App\Models\Club;
use App\Models\ClubInvitation;
use App\Models\User;

class ClubInvitationPolicy
{
    public function viewAny(User $user, Club $club): bool
    {
        return $this->isMemberOfClub($user, $club);
    }

    public function create(User $user, Club $club): bool
    {
        return $this->isMemberOfClub($user, $club);
    }

    public function delete(User $user, ClubInvitation $invitation): bool
    {
        return $this->isMemberOfClub($user, $invitation->club);
    }

    private function isMemberOfClub(User $user, Club $club): bool
    {
        return $user->clubs()->whereKey($club->getKey())->exists();
    }
}
