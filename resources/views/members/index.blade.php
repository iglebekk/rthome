<x-layouts.app :title="__('members.title')" :$club>
    <x-app.page-header :title="__('members.title')" :description="__('members.description')" :eyebrow="$club->name">
        <x-slot:actions><x-app.link-button :href="route('clubs.members.create', $club)" icon="plus">{{ __('members.actions.create') }}</x-app.link-button></x-slot:actions>
    </x-app.page-header>
    <x-members.list :$club :$members />
</x-layouts.app>
