<x-layouts.app :title="__('positions.title')" :$club>
    <x-app.page-header :title="__('positions.title')" :description="__('positions.description')" :eyebrow="$club->name"><x-slot:actions><x-app.link-button :href="route('clubs.positions.create', $club)" icon="plus">{{ __('positions.actions.create') }}</x-app.link-button></x-slot:actions></x-app.page-header>
    <x-positions.list :$club :$positions />
</x-layouts.app>
