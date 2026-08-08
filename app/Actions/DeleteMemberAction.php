<?php

namespace App\Actions;

use App\Models\Club;
use App\Models\Member;
use Illuminate\Support\Facades\DB;

class DeleteMemberAction
{
    public function handle(Club $club, Member $member): void
    {
        DB::transaction(function () use ($club, $member): void {
            if ($club->members()->count() === 1) {
                $club->delete();

                return;
            }

            $member->delete();
        });
    }
}
