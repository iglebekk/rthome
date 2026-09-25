<x-layouts.app :title="__('events.view_title')" :$club>
    <x-events.details
        :$event
        :$club
        :back-url="route('clubs.events.index', $club)"
        :edit-url="route('clubs.events.edit', [$club, $event])"
        :$taktCalendarUrl
        :description="__('events.view_description')"
    />
</x-layouts.app>
