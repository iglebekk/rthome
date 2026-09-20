<x-layouts.app :title="__('invoices.created_title')" :$club>
    <x-app.page-header
        :title="__('invoices.created_title')"
        :description="__('invoices.created_description', ['count' => $creation->invoices->count()])"
        :eyebrow="$club->name"
    >
        <x-slot:actions>
            <x-app.link-button :href="route('clubs.invoice-creations.create', $club)" icon="plus">
                {{ __('invoices.actions.create_another') }}
            </x-app.link-button>
        </x-slot:actions>
    </x-app.page-header>
    <x-invoices.list :$club :invoices="$creation->invoices" show-send />
</x-layouts.app>
