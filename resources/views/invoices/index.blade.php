<x-layouts.app :title="__('invoices.title')" :$club>
    <x-app.page-header :title="__('invoices.title')" :description="__('invoices.description')" :eyebrow="$club->name">
        <x-slot:actions>
            <livewire:invoices.go-to-invoice />
            <x-app.link-button :href="route('clubs.invoice-creations.create', $club)" icon="plus">{{ __('invoices.actions.create_one') }}</x-app.link-button>
        </x-slot:actions>
    </x-app.page-header>
    <x-invoice-creations.list :$club :$drafts />
    <x-form :action="route('clubs.invoices.index', $club)" method="GET" class="sm:grid-cols-[1fr_1fr_auto] sm:items-end">
        <x-form.select name="settlement" :label="__('invoices.filters.settlement')" :options="__('invoices.filters.settlements')" :value="$settlement" />
        <x-form.select name="sort" :label="__('invoices.filters.sort')" :options="__('invoices.filters.sorts')" :value="$sort" />
        <x-app.button type="submit">{{ __('invoices.filters.apply') }}</x-app.button>
    </x-form>
    <x-invoices.list :$club :$invoices :$settlement />
</x-layouts.app>
