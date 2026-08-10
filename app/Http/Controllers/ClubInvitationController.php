<?php

namespace App\Http\Controllers;

use App\Actions\AcceptClubInvitationAction;
use App\Http\Requests\StoreClubInvitationRequest;
use App\Models\ClubInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ClubInvitationController extends Controller
{
    public function index(Request $request, int $club): View
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        Gate::authorize('viewAny', [ClubInvitation::class, $clubModel]);

        return view('clubs.settings.invitations', [
            'club' => $clubModel,
            'invitations' => $clubModel->invitations()->latest()->get(),
        ]);
    }

    public function store(StoreClubInvitationRequest $request, int $club): RedirectResponse
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        Gate::authorize('create', [ClubInvitation::class, $clubModel]);
        $tokenHash = hash('sha256', Str::random(64));

        $invitation = $clubModel->invitations()->create([
            'created_by_user_id' => $request->user()->getKey(),
            'token_hash' => $tokenHash,
            'name' => $request->validated('name'),
            'expires_at' => now()->addDays((int) $request->validated('days')),
        ]);

        $url = route('club-invitations.show', ['token' => $invitation->token_hash]);

        return redirect()->route('clubs.settings.invitations', $clubModel)->with('invitation_url', $url);
    }

    public function destroy(Request $request, ClubInvitation $invitation): RedirectResponse
    {
        $invitation->load('club');
        Gate::authorize('delete', $invitation);
        $invitation->update(['revoked_at' => now()]);

        return redirect()->route('clubs.settings.invitations', $invitation->club)->with('status', __('clubs.settings.invitations.messages.revoked'));
    }

    public function show(Request $request, string $token, AcceptClubInvitationAction $acceptInvitation): View|RedirectResponse
    {
        $invitation = $acceptInvitation->find($token);
        abort_if($invitation === null, 404);
        $request->session()->put('club_invitation.token', $token);

        if ($request->user() === null) {
            return view('club-invitations.show', ['invitation' => $invitation, 'token' => $token]);
        }

        if (! $request->user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        if ($invitation->club->members()->where('user_id', $request->user()->getKey())->exists()) {
            return redirect()->route('clubs.dashboard', $invitation->club)->with('status', __('clubs.settings.invitations.messages.already_member'));
        }

        return view('club-invitations.show', ['invitation' => $invitation, 'token' => $token]);
    }

    public function confirm(Request $request, string $token, AcceptClubInvitationAction $acceptInvitation): RedirectResponse
    {
        abort_unless($request->user()?->hasVerifiedEmail(), 403);
        $club = $acceptInvitation->handle($request->user(), $token);
        abort_if($club === null, 404);
        $request->session()->forget('club_invitation.token');

        return redirect()->route('clubs.dashboard', $club)->with('status', __('clubs.settings.invitations.messages.joined'));
    }
}
