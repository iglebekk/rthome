<?php

namespace App\Policies;

use App\Models\Club;
use App\Models\Member;
use App\Models\User;

class MemberPolicy
{
    public function create(User $user, Club $club): bool
    {
        return $this->isMemberOfClub($user, $club->getKey());
    }

    public function update(User $user, Member $member): bool
    {
        return $this->isMemberOfClub($user, $member->club_id);
    }

    public function delete(User $user, Member $member): bool
    {
        if (! $this->isMemberOfClub($user, $member->club_id)) {
            return false;
        }

        $club = $member->club()->withCount(['members', 'invoices'])->first();

        return $club !== null && ! ($club->members_count === 1 && $club->invoices_count > 0);
    }

    private function isMemberOfClub(User $user, int $clubId): bool
    {
        return $user->clubs()->whereKey($clubId)->exists();
    }
}
