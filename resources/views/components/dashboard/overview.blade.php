@props(['club', 'memberCount', 'upcomingEvents', 'nextEvent', 'nextEventCountdown', 'filledPositions', 'links'])

<div class="grid gap-10">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <x-app.metric-card :label="__('dashboard.metrics.members')" :value="$memberCount" :description="__('dashboard.metrics.members_description')" icon="users" />
        @if ($nextEvent)
            <x-app.metric-card :label="__('dashboard.metrics.next_event')" :value="$nextEventCountdown" :description="__('dashboard.metrics.next_event_description', ['event' => $nextEvent->name])" icon="calendar-days" />
        @else
            <x-app.empty-state :title="__('dashboard.empty_next_event')" :description="__('dashboard.empty_next_event_description')" icon="calendar-days">
                <x-slot:action><x-app.link-button :href="route('clubs.events.create', $club)" icon="plus">{{ __('events.actions.create') }}</x-app.link-button></x-slot:action>
            </x-app.empty-state>
        @endif
        <x-app.metric-card :label="__('dashboard.metrics.upcoming_events')" :value="$upcomingEvents->count()" :description="__('dashboard.metrics.upcoming_events_description')" icon="calendar-days" />
    </div>

    <x-story.media-panel :title="__('dashboard.quick_actions')" :description="__('dashboard.description')">
        <div class="flex flex-wrap gap-3">
            <x-app.link-button :href="route('clubs.members.create', $club)" icon="user-plus">{{ __('members.actions.create') }}</x-app.link-button>
            <x-app.link-button :href="route('clubs.events.create', $club)" variant="ghost" icon="calendar-days" data-test="dashboard-secondary-action" class="text-white! hover:bg-white/15! hover:text-white!">{{ __('events.actions.create') }}</x-app.link-button>
            <x-app.link-button :href="route('clubs.links.create', $club)" variant="ghost" icon="link" class="text-white! hover:bg-white/15! hover:text-white!">{{ __('links.actions.create') }}</x-app.link-button>
        </div>
    </x-story.media-panel>

    <div class="grid gap-8 xl:grid-cols-2">
        <x-app.section :title="__('dashboard.upcoming')">
            <div class="grid gap-3">
                @forelse ($upcomingEvents as $event)
                    <x-dashboard.event :$event :$club />
                @empty
                    <x-app.empty-state :title="__('dashboard.empty_events')" :description="__('dashboard.empty_events_description')" icon="calendar-days">
                        <x-slot:action><x-app.link-button :href="route('clubs.events.create', $club)" icon="plus">{{ __('events.actions.create') }}</x-app.link-button></x-slot:action>
                    </x-app.empty-state>
                @endforelse
            </div>
            @if ($upcomingEvents->isNotEmpty())
                <x-app.text-link :href="route('clubs.events.index', $club)">{{ __('dashboard.view_all_events') }}</x-app.text-link>
            @endif
        </x-app.section>
        <x-app.section :title="__('dashboard.links')">
            <div class="grid gap-3">
                @forelse ($links as $link)
                    <x-links.card :$link :$club />
                @empty
                    <x-app.empty-state :title="__('dashboard.empty_links')" :description="__('dashboard.empty_links_description')" icon="link">
                        <x-slot:action><x-app.link-button :href="route('clubs.links.create', $club)" icon="plus">{{ __('links.actions.create') }}</x-app.link-button></x-slot:action>
                    </x-app.empty-state>
                @endforelse
            </div>
            @if ($links->isNotEmpty())
                <x-app.text-link :href="route('clubs.links.index', $club)">{{ __('dashboard.view_all_links') }}</x-app.text-link>
            @endif
        </x-app.section>
        <x-app.section :title="__('positions.title')" class="xl:col-span-2">
            <div class="grid grid-cols-[repeat(auto-fit,minmax(200px,280px))] gap-3" data-test="dashboard-positions-grid">
                @forelse ($filledPositions as $position)
                    <x-app.position-card :$position :$club member-first />
                @empty
                    <x-app.empty-state class="col-span-full" :title="__('dashboard.empty_filled_positions')" :description="__('dashboard.empty_filled_positions_description')" icon="briefcase" />
                @endforelse
            </div>
        </x-app.section>
    </div>
</div>
