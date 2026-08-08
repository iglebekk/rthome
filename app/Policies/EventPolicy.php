<?php

namespace App\Policies;

use App\Models\Club;
use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function create(User $user, Club $club): bool
    {
        return $this->isMemberOfClub($user, $club->getKey());
    }

    public function update(User $user, Event $event): bool
    {
        return $this->isMemberOfClub($user, $event->club_id);
    }

    public function delete(User $user, Event $event): bool
    {
        return $this->isMemberOfClub($user, $event->club_id);
    }

    private function isMemberOfClub(User $user, int $clubId): bool
    {
        return $user->clubs()->whereKey($clubId)->exists();
    }
}
