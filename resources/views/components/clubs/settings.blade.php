@props(['club'])

<div class="grid gap-8">
    <x-clubs.form :$club />
    <x-app.danger-zone :title="__('clubs.settings.danger_title')" :description="__('clubs.settings.danger_description')">
        <x-app.dialog
            name="delete-club"
            :title="__('clubs.settings.danger_title')"
            :description="__('clubs.settings.danger_description')"
            :confirm-label="__('clubs.settings.delete')"
            :action="route('clubs.destroy', $club)"
        >
            <x-slot:trigger><x-app.button variant="danger" icon="trash">{{ __('clubs.settings.delete') }}</x-app.button></x-slot:trigger>
            <x-slot:fields><x-form.input name="club_name" :label="__('clubs.settings.confirm_label')" required /></x-slot:fields>
        </x-app.dialog>
    </x-app.danger-zone>
</div>
