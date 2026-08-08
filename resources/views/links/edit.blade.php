<x-layouts.app :title="__('links.edit_title')" :$club>
    <x-app.page-header :title="__('links.edit_title')" :description="__('links.edit_description')" :eyebrow="$club->name">
        <x-slot:actions><x-app.dialog :name="'delete-link-'.$link->getKey()" :title="__('links.delete_title', ['name' => $link->name])" :description="__('links.delete_description')" :confirm-label="__('links.actions.delete')" :action="route('clubs.links.destroy', [$club, $link])"><x-slot:trigger><x-app.button variant="danger" icon="trash">{{ __('links.actions.delete') }}</x-app.button></x-slot:trigger></x-app.dialog></x-slot:actions>
    </x-app.page-header>
    <x-links.form :$club :$link />
</x-layouts.app>
