<x-layouts.public :title="$event->name">
    <x-events.details
        :$event
        :$club
        :back-url="route('public-events.index', $club->public_events_token)"
        :$taktCalendarUrl
        :description="__('events.public_sharing.view_description')"
    />
</x-layouts.public>
