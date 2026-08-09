<?php

namespace App\Http\Controllers\Auth;

use App\Actions\CreateMemberActivationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\IdentifyAccountRequest;
use App\Models\Member;
use App\Models\User;
use App\Notifications\MemberActivationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class IdentifyAccountController extends Controller
{
    public function __invoke(
        IdentifyAccountRequest $request,
        CreateMemberActivationAction $createMemberActivation,
    ): RedirectResponse {
        $email = $request->string('email')->trim()->lower()->toString();

        if (User::query()->where('email', $email)->exists()) {
            $request->session()->put('login.email', $email);

            return redirect()->route('login');
        }

        if (Member::query()->whereNull('user_id')->where('email', $email)->exists()) {
            ['activation' => $activation, 'plainToken' => $plainToken] = $createMemberActivation->handle($email);

            Notification::route('mail', $email)
                ->notify(new MemberActivationNotification($activation, $plainToken));

            return redirect()->route('activation.sent')->with('activation_email', $email);
        }

        throw ValidationException::withMessages([
            'email' => __('auth.identify.unavailable'),
        ]);
    }
}
