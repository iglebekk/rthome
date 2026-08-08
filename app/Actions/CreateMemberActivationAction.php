<?php

namespace App\Actions;

use App\Models\MemberActivation;
use Illuminate\Support\Str;

class CreateMemberActivationAction
{
    /** @return array{activation: MemberActivation, plainToken: string} */
    public function handle(string $email): array
    {
        $plainToken = Str::random(64);

        MemberActivation::query()
            ->where('email', $email)
            ->whereNull('used_at')
            ->delete();

        $activation = MemberActivation::query()->create([
            'email' => $email,
            'token' => hash('sha256', $plainToken),
            'expires_at' => now()->addMinutes(60),
        ]);

        return compact('activation', 'plainToken');
    }
}
