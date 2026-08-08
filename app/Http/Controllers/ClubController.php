<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClubRequest;
use App\Models\Club;
use Illuminate\Http\RedirectResponse;

class ClubController extends Controller
{
    public function store(StoreClubRequest $request): RedirectResponse
    {
        $user = $request->user();
        $club = Club::query()->create($request->validated());

        $club->members()->create([
            'user_id' => $user->getKey(),
            'name' => $user->name,
            'email' => $user->email,
        ]);

        return back();
    }
}
