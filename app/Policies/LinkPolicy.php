<?php

namespace App\Policies;

use App\Models\Club;
use App\Models\Link;
use App\Models\User;

class LinkPolicy
{
    public function create(User $user, Club $club): bool
    {
        return $this->isMemberOfClub($user, $club->getKey());
    }

    public function update(User $user, Link $link): bool
    {
        return $this->isMemberOfClub($user, $link->club_id);
    }

    public function delete(User $user, Link $link): bool
    {
        return $this->isMemberOfClub($user, $link->club_id);
    }

    private function isMemberOfClub(User $user, int $clubId): bool
    {
        return $user->clubs()->whereKey($clubId)->exists();
    }
}
