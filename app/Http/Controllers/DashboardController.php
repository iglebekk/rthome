<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function home(Request $request): RedirectResponse
    {
        $club = $request->user()->clubs()->orderBy('clubs.name')->first();

        return $club === null
            ? redirect()->route('clubs.create')
            : redirect()->route('clubs.dashboard', $club);
    }

    public function show(Request $request, int $club): View
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $upcomingEvents = $clubModel->events()
            ->upcoming()
            ->oldest('starts_at')
            ->limit(3)
            ->get();
        $nextEvent = $upcomingEvents->first();
        $filledPositions = $clubModel->positions()
            ->whereNotNull('member_id')
            ->with('member')
            ->orderBy('sort_order')
            ->limit(5)
            ->get();
        $links = $clubModel->links()
            ->orderByDesc('is_pinned')
            ->latest()
            ->limit(10)
            ->get();

        return view('dashboard', [
            'club' => $clubModel,
            'upcomingEvents' => $upcomingEvents,
            'nextEvent' => $nextEvent,
            'nextEventCountdown' => $nextEvent?->starts_at->diffForHumans(),
            'filledPositions' => $filledPositions,
            'links' => $links,
            'memberCount' => $clubModel->members()->count(),
        ]);
    }
}
