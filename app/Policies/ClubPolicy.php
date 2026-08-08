<?php

namespace App\Policies;

use App\Models\Club;
use App\Models\User;

class ClubPolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Club $club): bool
    {
        return $this->isMember($user, $club);
    }

    public function delete(User $user, Club $club): bool
    {
        return $this->isMember($user, $club);
    }

    private function isMember(User $user, Club $club): bool
    {
        return $user->clubs()->whereKey($club->getKey())->exists();
    }
}
