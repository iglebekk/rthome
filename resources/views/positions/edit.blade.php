<x-layouts.app :title="__('positions.edit_title')" :$club>
    <x-app.page-header :title="__('positions.edit_title')" :description="__('positions.edit_description')" :eyebrow="$club->name"><x-slot:actions><x-app.dialog :name="'delete-position-'.$position->getKey()" :title="__('positions.delete_title', ['name' => $position->name])" :description="__('positions.delete_description')" :confirm-label="__('positions.actions.delete')" :action="route('clubs.positions.destroy', [$club, $position])"><x-slot:trigger><x-app.button variant="danger" icon="trash">{{ __('positions.actions.delete') }}</x-app.button></x-slot:trigger></x-app.dialog></x-slot:actions></x-app.page-header>
    <x-positions.form :$club :$position :$memberOptions />
</x-layouts.app>
