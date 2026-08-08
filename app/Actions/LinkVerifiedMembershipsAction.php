<?php

namespace App\Actions;

use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LinkVerifiedMembershipsAction
{
    public function handle(User $user): void
    {
        if (! $user->hasVerifiedEmail()) {
            return;
        }

        DB::transaction(function () use ($user): void {
            $linkedMembers = $user->members()->get(['id', 'club_id'])->keyBy('club_id');
            $matchingMembers = Member::query()
                ->whereNull('user_id')
                ->where('email', $user->email)
                ->get();

            foreach ($matchingMembers as $matchingMember) {
                $linkedMember = $linkedMembers->get($matchingMember->club_id);

                if ($linkedMember === null) {
                    $matchingMember->update(['user_id' => $user->getKey()]);

                    continue;
                }

                $matchingMember->positions()->update(['member_id' => $linkedMember->getKey()]);
                $matchingMember->delete();
            }

            $user->members()->update(['email' => $user->email]);
        });
    }
}
