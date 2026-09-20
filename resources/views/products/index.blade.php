<x-layouts.app :title="__('products.title')" :$club>
    <x-app.page-header :title="__('products.title')" :description="__('products.description')" :eyebrow="$club->name">
        <x-slot:actions><x-app.link-button :href="route('clubs.products.create', $club)" icon="plus">{{ __('products.actions.create') }}</x-app.link-button></x-slot:actions>
    </x-app.page-header>
    <x-products.list :$club :$products />
</x-layouts.app>
