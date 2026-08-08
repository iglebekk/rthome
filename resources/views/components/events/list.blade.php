@props(['club', 'upcomingEvents', 'pastEvents'])

<div class="grid gap-10">
    <x-app.section :title="__('events.upcoming')">
        @if ($upcomingEvents->isEmpty())
            <x-story.spotlight-empty-state :title="__('events.empty_upcoming')" :description="__('events.empty_upcoming_description')">
                <x-slot:action><x-app.link-button :href="route('clubs.events.create', $club)" icon="plus">{{ __('events.actions.create') }}</x-app.link-button></x-slot:action>
            </x-story.spotlight-empty-state>
        @else
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">@foreach ($upcomingEvents as $event)<x-app.event-card :$event :$club />@endforeach</div>
            <x-app.pagination :paginator="$upcomingEvents" />
        @endif
    </x-app.section>

    <x-app.section :title="__('events.past')">
        @if ($pastEvents->isEmpty())
            <x-app.empty-state :title="__('events.empty_past')" :description="__('events.empty_past_description')" icon="archive-box" />
        @else
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">@foreach ($pastEvents as $event)<x-app.event-card :$event :$club />@endforeach</div>
            <x-app.pagination :paginator="$pastEvents" />
        @endif
    </x-app.section>
</div>
