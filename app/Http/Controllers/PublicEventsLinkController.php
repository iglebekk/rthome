<?php

namespace App\Http\Controllers;

use App\Actions\EnablePublicEventsSharingAction;
use App\Http\Requests\ManagePublicEventsLinkRequest;
use Illuminate\Http\RedirectResponse;

class PublicEventsLinkController extends Controller
{
    public function store(
        ManagePublicEventsLinkRequest $request,
        int $club,
        EnablePublicEventsSharingAction $enablePublicEventsSharing,
    ): RedirectResponse {
        $clubModel = $request->user()->clubs()->findOrFail($club);

        $enablePublicEventsSharing->handle($clubModel);

        return redirect()->route('clubs.events.index', $clubModel)
            ->with('status', __('events.public_sharing.messages.enabled'));
    }

    public function destroy(ManagePublicEventsLinkRequest $request, int $club): RedirectResponse
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);

        $clubModel->update(['public_events_enabled' => false]);

        return redirect()->route('clubs.events.index', $clubModel)
            ->with('status', __('events.public_sharing.messages.disabled'));
    }
}
