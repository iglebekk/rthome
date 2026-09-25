<?php

namespace App\Actions;

use App\Models\Club;
use Illuminate\Support\Str;

class EnablePublicEventsSharingAction
{
    public function handle(Club $club): void
    {
        $club->update([
            'public_events_token' => $club->public_events_token ?? Str::random(64),
            'public_events_enabled' => true,
        ]);
    }
}
