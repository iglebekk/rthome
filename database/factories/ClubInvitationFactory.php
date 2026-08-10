<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\ClubInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<ClubInvitation> */
class ClubInvitationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'club_id' => Club::factory(),
            'created_by_user_id' => User::factory(),
            'token_hash' => hash('sha256', Str::random(64)),
            'name' => null,
            'expires_at' => now()->addDays(7),
            'revoked_at' => null,
        ];
    }
}
