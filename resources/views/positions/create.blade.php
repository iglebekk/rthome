<x-layouts.app :title="__('positions.create_title')" :$club>
    <x-app.page-header :title="__('positions.create_title')" :description="__('positions.create_description')" :eyebrow="$club->name" />
    <x-positions.form :$club :$memberOptions />
</x-layouts.app>
