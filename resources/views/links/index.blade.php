<x-layouts.app :title="__('links.title')" :$club>
    <x-app.page-header :title="__('links.title')" :description="__('links.description')" :eyebrow="$club->name">
        <x-slot:actions><x-app.link-button :href="route('clubs.links.create', $club)" icon="plus">{{ __('links.actions.create') }}</x-app.link-button></x-slot:actions>
    </x-app.page-header>
    <x-links.list :$club :$links />
</x-layouts.app>
