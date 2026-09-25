<?php

namespace App\Http\Controllers;

use App\Actions\BuildTaktCalendarUrlAction;
use App\Models\Club;
use Illuminate\View\View;

class PublicEventController extends Controller
{
    public function index(string $token): View
    {
        $club = $this->resolveClub($token);

        return view('public-events.index', [
            'club' => $club,
            'upcomingEvents' => $club->events()
                ->upcoming()
                ->orderBy('starts_at')
                ->orderBy('id')
                ->paginate(12),
        ]);
    }

    public function show(
        string $token,
        int $event,
        BuildTaktCalendarUrlAction $buildTaktCalendarUrl,
    ): View {
        $club = $this->resolveClub($token);
        $eventModel = $club->events()->upcoming()->findOrFail($event);

        return view('public-events.show', [
            'club' => $club,
            'event' => $eventModel,
            'taktCalendarUrl' => $buildTaktCalendarUrl->handle($eventModel),
        ]);
    }

    private function resolveClub(string $token): Club
    {
        return Club::query()
            ->where('public_events_token', $token)
            ->where('public_events_enabled', true)
            ->firstOrFail();
    }
}
