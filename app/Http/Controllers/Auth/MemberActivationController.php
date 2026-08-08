<?php

namespace App\Http\Controllers\Auth;

use App\Actions\LinkVerifiedMembershipsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ActivateMemberRequest;
use App\Models\MemberActivation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class MemberActivationController extends Controller
{
    public function show(Request $request, MemberActivation $activation, string $token): View
    {
        abort_unless($activation->isUsable() && hash_equals($activation->token, hash('sha256', $token)), 404);

        return view('auth.activate', [
            'activation' => $activation,
            'token' => $token,
            'activationAction' => $request->fullUrl(),
        ]);
    }

    public function store(
        ActivateMemberRequest $request,
        MemberActivation $activation,
        string $token,
        LinkVerifiedMembershipsAction $linkMemberships,
    ): RedirectResponse {
        abort_unless($activation->isUsable() && hash_equals($activation->token, hash('sha256', $token)), 404);

        $user = DB::transaction(function () use ($request, $activation, $token): User {
            $activation = MemberActivation::query()->lockForUpdate()->findOrFail($activation->getKey());
            abort_unless($activation->isUsable() && hash_equals($activation->token, hash('sha256', $token)), 404);

            $user = User::query()->create([
                'name' => $request->string('name')->toString(),
                'email' => $activation->email,
                'password' => Hash::make($request->string('password')->toString()),
            ]);
            $user->markEmailAsVerified();

            $activation->update(['used_at' => now()]);

            return $user;
        });

        $linkMemberships->handle($user);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('status', __('auth.activation.completed'));
    }
}
