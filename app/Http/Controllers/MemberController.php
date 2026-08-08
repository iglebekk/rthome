<?php

namespace App\Http\Controllers;

use App\Actions\DeleteMemberAction;
use App\Http\Requests\DestroyMemberRequest;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request, int $club): View
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $members = $clubModel->members()
            ->with(['positions' => fn ($query) => $query->orderBy('sort_order')])
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->trim()->toString();
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('members.index', ['club' => $clubModel, 'members' => $members]);
    }

    public function create(Request $request, int $club): View
    {
        return view('members.create', [
            'club' => $request->user()->clubs()->findOrFail($club),
        ]);
    }

    public function store(StoreMemberRequest $request, int $club): RedirectResponse
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $clubModel->members()->create($request->validated());

        return redirect()->route('clubs.members.index', $clubModel)
            ->with('status', __('members.messages.created'));
    }

    public function edit(Request $request, int $club, int $member): View
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $memberModel = $clubModel->members()->findOrFail($member);

        return view('members.edit', ['club' => $clubModel, 'member' => $memberModel]);
    }

    public function update(UpdateMemberRequest $request, int $club, int $member): RedirectResponse
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $memberModel = $clubModel->members()->findOrFail($member);
        $memberModel->update($request->validated());

        return redirect()->route('clubs.members.index', $clubModel)
            ->with('status', __('members.messages.updated'));
    }

    public function destroy(
        DestroyMemberRequest $request,
        int $club,
        int $member,
        DeleteMemberAction $deleteMember,
    ): RedirectResponse {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $memberModel = $clubModel->members()->findOrFail($member);
        $deletesClub = $clubModel->members()->count() === 1;

        $deleteMember->handle($clubModel, $memberModel);

        return redirect()
            ->route($deletesClub ? 'home' : 'clubs.members.index', $deletesClub ? [] : $clubModel)
            ->with('status', __($deletesClub ? 'clubs.messages.deleted_with_last_member' : 'members.messages.deleted'));
    }
}
