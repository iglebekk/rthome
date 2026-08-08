<x-layouts.app :title="__('events.create_title')" :$club>
    <x-app.page-header :title="__('events.create_title')" :description="__('events.create_description')" :eyebrow="$club->name" />
    <x-events.form :$club />
</x-layouts.app>
