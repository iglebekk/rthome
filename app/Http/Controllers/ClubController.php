<?php

namespace App\Http\Controllers;

use App\Actions\ImportEventsAction;
use App\Http\Requests\DestroyClubRequest;
use App\Http\Requests\ImportEventsRequest;
use App\Http\Requests\StoreClubRequest;
use App\Http\Requests\UpdateClubRequest;
use App\Models\Club;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClubController extends Controller
{
    public function index(Request $request): View
    {
        $clubs = $request->user()->clubs()->orderBy('clubs.name')->get();

        return view('clubs.index', ['clubs' => $clubs]);
    }

    public function create(): View
    {
        return view('clubs.create');
    }

    public function store(StoreClubRequest $request): RedirectResponse
    {
        $user = $request->user();
        $club = Club::query()->create($request->validated());

        $club->members()->create([
            'user_id' => $user->getKey(),
            'name' => $user->name,
            'email' => $user->email,
        ]);

        return redirect()
            ->route('clubs.dashboard', $club)
            ->with('status', __('clubs.messages.created'));
    }

    public function edit(Request $request, int $club): View
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);

        return view('clubs.edit', ['club' => $clubModel]);
    }

    public function settingsEvents(Request $request, int $club): View
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);

        return view('clubs.settings.events', ['club' => $clubModel]);
    }

    public function importEvents(ImportEventsRequest $request, ImportEventsAction $importEvents, int $club): RedirectResponse
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $count = $importEvents->handle($clubModel, $request->validated('events'));

        return redirect()
            ->route('clubs.settings.events', $clubModel)
            ->with('status', trans_choice('clubs.settings.events.import.messages.imported', $count, ['count' => $count]));
    }

    public function update(UpdateClubRequest $request, int $club): RedirectResponse
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $clubModel->update($request->validated());

        return redirect()
            ->route('clubs.edit', $clubModel)
            ->with('status', __('clubs.messages.updated'));
    }

    public function destroy(DestroyClubRequest $request, int $club): RedirectResponse
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $clubModel->delete();

        return redirect()->route('home')->with('status', __('clubs.messages.deleted'));
    }
}
