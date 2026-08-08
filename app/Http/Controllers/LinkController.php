<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyLinkRequest;
use App\Http\Requests\StoreLinkRequest;
use App\Http\Requests\UpdateLinkRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LinkController extends Controller
{
    public function index(Request $request, int $club): View
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);

        return view('links.index', [
            'club' => $clubModel,
            'links' => $clubModel->links()
                ->orderByDesc('is_pinned')
                ->latest()
                ->paginate(20),
        ]);
    }

    public function create(Request $request, int $club): View
    {
        return view('links.create', [
            'club' => $request->user()->clubs()->findOrFail($club),
        ]);
    }

    public function store(StoreLinkRequest $request, int $club): RedirectResponse
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $clubModel->links()->create($request->validated());

        return redirect()->route('clubs.links.index', $clubModel)
            ->with('status', __('links.messages.created'));
    }

    public function edit(Request $request, int $club, int $link): View
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);

        return view('links.edit', [
            'club' => $clubModel,
            'link' => $clubModel->links()->findOrFail($link),
        ]);
    }

    public function update(UpdateLinkRequest $request, int $club, int $link): RedirectResponse
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $linkModel = $clubModel->links()->findOrFail($link);
        $linkModel->update($request->validated());

        return redirect()->route('clubs.links.index', $clubModel)
            ->with('status', __('links.messages.updated'));
    }

    public function destroy(DestroyLinkRequest $request, int $club, int $link): RedirectResponse
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $clubModel->links()->findOrFail($link)->delete();

        return redirect()->route('clubs.links.index', $clubModel)
            ->with('status', __('links.messages.deleted'));
    }
}
