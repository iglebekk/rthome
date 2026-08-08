<x-layouts.app :title="__('profile.title')" :backToClub="true">
    <x-app.page-header :title="__('profile.title')" :description="__('profile.description')" />
    <x-profile.settings :$user />
</x-layouts.app>
