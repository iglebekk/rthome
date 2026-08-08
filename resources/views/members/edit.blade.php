<x-layouts.app :title="__('members.edit_title')" :$club>
    <x-app.page-header :title="__('members.edit_title')" :description="__('members.edit_description')" :eyebrow="$club->name" />
    <x-members.form :$club :$member />
</x-layouts.app>
