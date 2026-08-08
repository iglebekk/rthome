<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyEventRequest;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request, int $club): AnonymousResourceCollection|View
    {
        $user = $request->user();

        abort_if($user === null, Response::HTTP_FORBIDDEN);

        $clubModel = $user->clubs()->findOrFail($club);
        if ($request->expectsJson()) {
            $events = $clubModel->events()
                ->upcoming()
                ->oldest('starts_at')
                ->paginate(25);

            return EventResource::collection($events);
        }

        return view('events.index', [
            'club' => $clubModel,
            'upcomingEvents' => $clubModel->events()->upcoming()->oldest('starts_at')->paginate(12, ['*'], 'upcoming'),
            'pastEvents' => $clubModel->events()->where('starts_at', '<', now())->latest('starts_at')->paginate(12, ['*'], 'past'),
        ]);
    }

    public function create(Request $request, int $club): View
    {
        return view('events.create', [
            'club' => $request->user()->clubs()->findOrFail($club),
        ]);
    }

    public function show(Request $request, int $club, int $event): View
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);

        return view('events.show', [
            'club' => $clubModel,
            'event' => $clubModel->events()->findOrFail($event),
        ]);
    }

    public function store(StoreEventRequest $request, int $club): JsonResponse|RedirectResponse
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $attributes = $this->normalizedAttributes($request->validated());

        if ($request->hasFile('image')) {
            $attributes['image_path'] = $request->file('image')->store('event-images', 'public');
        }

        unset($attributes['image']);

        $event = $clubModel->events()->create($attributes);

        if (! $request->expectsJson()) {
            return redirect()->route('clubs.events.index', $clubModel)
                ->with('status', __('events.messages.created'));
        }

        return (new EventResource($event))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function edit(Request $request, int $club, int $event): View
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);

        return view('events.edit', [
            'club' => $clubModel,
            'event' => $clubModel->events()->findOrFail($event),
        ]);
    }

    public function update(UpdateEventRequest $request, int $club, int $event): EventResource|RedirectResponse
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $eventModel = $clubModel->events()->findOrFail($event);
        $attributes = $this->normalizedAttributes($request->validated());
        $oldImagePath = $eventModel->image_path;

        if ($request->hasFile('image')) {
            $attributes['image_path'] = $request->file('image')->store('event-images', 'public');
        } elseif ($request->boolean('remove_image')) {
            $attributes['image_path'] = null;
        }

        unset($attributes['image'], $attributes['remove_image']);

        $eventModel->update($attributes);

        if ($oldImagePath !== null && $oldImagePath !== $eventModel->image_path) {
            Storage::disk('public')->delete($oldImagePath);
        }

        if (! $request->expectsJson()) {
            return redirect()->route('clubs.events.show', [$clubModel, $eventModel])
                ->with('status', __('events.messages.updated'));
        }

        return new EventResource($eventModel);
    }

    public function destroy(DestroyEventRequest $request, int $club, int $event): Response|RedirectResponse
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $eventModel = $clubModel->events()->findOrFail($event);

        $eventModel->delete();

        if (! $request->expectsJson()) {
            return redirect()->route('clubs.events.index', $clubModel)
                ->with('status', __('events.messages.deleted'));
        }

        return response()->noContent();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function normalizedAttributes(array $attributes): array
    {
        foreach (['starts_at', 'ends_at'] as $attribute) {
            if (isset($attributes[$attribute])) {
                $attributes[$attribute] = CarbonImmutable::parse($attributes[$attribute])->utc();
            }
        }

        return $attributes;
    }
}
