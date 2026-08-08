<x-layouts.app :title="__('events.edit_title')" :$club>
    <x-app.page-header :title="__('events.edit_title')" :description="__('events.edit_description')" :eyebrow="$club->name"><x-slot:actions><x-app.dialog :name="'delete-event-'.$event->getKey()" :title="__('events.delete_title', ['name' => $event->name])" :description="__('events.delete_description')" :confirm-label="__('events.actions.delete')" :action="route('clubs.events.destroy', [$club, $event])"><x-slot:trigger><x-app.button variant="danger" icon="trash">{{ __('events.actions.delete') }}</x-app.button></x-slot:trigger></x-app.dialog></x-slot:actions></x-app.page-header>
    <x-events.form :$club :$event />
</x-layouts.app>
