<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyPositionRequest;
use App\Http\Requests\StorePositionRequest;
use App\Http\Requests\UpdatePositionRequest;
use App\Http\Resources\PositionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, int $club): AnonymousResourceCollection|View
    {
        $user = $request->user();

        abort_if($user === null, Response::HTTP_FORBIDDEN);

        $clubModel = $user->clubs()->findOrFail($club);
        $positions = $clubModel->positions()
            ->with('member')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $request->expectsJson()
            ? PositionResource::collection($positions)
            : view('positions.index', ['club' => $clubModel, 'positions' => $positions]);
    }

    public function create(Request $request, int $club): View
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);

        return view('positions.create', [
            'club' => $clubModel,
            'memberOptions' => $clubModel->members()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePositionRequest $request, int $club): JsonResponse|RedirectResponse
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $attributes = $request->validated();

        if (! array_key_exists('sort_order', $attributes)) {
            $lastSortOrder = $clubModel->positions()->max('sort_order');
            $attributes['sort_order'] = $lastSortOrder === null ? 0 : $lastSortOrder + 1;
        }

        $position = $clubModel->positions()->create($attributes)->load('member');

        if (! $request->expectsJson()) {
            return redirect()->route('clubs.positions.index', $clubModel)
                ->with('status', __('positions.messages.created'));
        }

        return (new PositionResource($position))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $club, int $position): PositionResource
    {
        $user = $request->user();

        abort_if($user === null, Response::HTTP_FORBIDDEN);

        $clubModel = $user->clubs()->findOrFail($club);
        $positionModel = $clubModel->positions()->with('member')->findOrFail($position);

        return new PositionResource($positionModel);
    }

    public function edit(Request $request, int $club, int $position): View
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);

        return view('positions.edit', [
            'club' => $clubModel,
            'position' => $clubModel->positions()->findOrFail($position),
            'memberOptions' => $clubModel->members()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdatePositionRequest $request,
        int $club,
        int $position,
    ): PositionResource|RedirectResponse {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $positionModel = $clubModel->positions()->findOrFail($position);

        $positionModel->update($request->validated());

        if (! $request->expectsJson()) {
            return redirect()->route('clubs.positions.index', $clubModel)
                ->with('status', __('positions.messages.updated'));
        }

        return new PositionResource($positionModel->load('member'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
        DestroyPositionRequest $request,
        int $club,
        int $position,
    ): Response|RedirectResponse {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $positionModel = $clubModel->positions()->findOrFail($position);

        $positionModel->delete();

        if (! $request->expectsJson()) {
            return redirect()->route('clubs.positions.index', $clubModel)
                ->with('status', __('positions.messages.deleted'));
        }

        return response()->noContent();
    }
}
