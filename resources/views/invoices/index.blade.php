<x-layouts.app :title="__('invoices.title')" :$club>
    <x-app.page-header :title="__('invoices.title')" :description="__('invoices.description')" :eyebrow="$club->name">
        <x-slot:actions><x-app.link-button :href="route('clubs.invoice-creations.create', $club)" icon="plus">{{ __('invoices.actions.create_one') }}</x-app.link-button></x-slot:actions>
    </x-app.page-header>
    <x-invoice-creations.list :$club :$drafts />
    <x-invoices.list :$club :$invoices />
</x-layouts.app>
