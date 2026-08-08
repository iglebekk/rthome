<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyMemberRequest;
use App\Models\Club;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;

class MemberController extends Controller {
    public function destroy(
        DestroyMemberRequest $request,
        Club $club,
        Member $member,
    ): RedirectResponse {
        if ($club->members()->count() === 1) {
            $club->delete();
        } else {
            $member->delete();
        }

        return back();
    }
}
