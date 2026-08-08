<x-layouts.app :title="__('clubs.settings.title')" :$club>
    <x-app.page-header :title="__('clubs.settings.title')" :description="__('clubs.settings.description')" :eyebrow="$club->name" />
    <x-clubs.settings :$club />
</x-layouts.app>
