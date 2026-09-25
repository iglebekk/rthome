@props(['club', 'upcomingEvents'])

<x-app.section :title="__('events.upcoming')">
    @if ($upcomingEvents->isEmpty())
        <x-app.empty-state
            :title="__('events.empty_upcoming')"
            :description="__('events.public_sharing.empty_upcoming_description')"
            icon="calendar-days"
        />
    @else
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($upcomingEvents as $event)
                <x-app.event-card
                    :$event
                    :url="route('public-events.show', ['token' => $club->public_events_token, 'event' => $event])"
                />
            @endforeach
        </div>
        <x-app.pagination :paginator="$upcomingEvents" />
    @endif
</x-app.section>
