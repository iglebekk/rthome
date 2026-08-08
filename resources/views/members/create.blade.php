<x-layouts.app :title="__('members.create_title')" :$club>
    <x-app.page-header :title="__('members.create_title')" :description="__('members.create_description')" :eyebrow="$club->name" />
    <x-members.form :$club />
</x-layouts.app>
