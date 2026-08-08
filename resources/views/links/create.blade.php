<x-layouts.app :title="__('links.create_title')" :$club>
    <x-app.page-header :title="__('links.create_title')" :description="__('links.create_description')" :eyebrow="$club->name" />
    <x-links.form :$club />
</x-layouts.app>
