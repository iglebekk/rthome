<x-layouts.app :title="__('events.title')" :$club>
    <x-app.page-header :title="__('events.title')" :description="__('events.description')" :eyebrow="$club->name"><x-slot:actions><x-app.link-button :href="route('clubs.events.create', $club)" icon="plus">{{ __('events.actions.create') }}</x-app.link-button></x-slot:actions></x-app.page-header>
    <x-events.list :$club :$upcomingEvents :$pastEvents />
</x-layouts.app>
