@props(['club', 'member' => null])

<x-app.card>
    <x-form :action="$member ? route('clubs.members.update', [$club, $member]) : route('clubs.members.store', $club)" :method="$member ? 'PUT' : 'POST'">
        <x-form.input name="name" :label="__('members.fields.name')" :value="old('name', $member?->name)" required autofocus />
        <x-form.input name="email" type="email" :label="__('members.fields.email')" :value="old('email', $member?->email)" :readonly="$member?->user_id !== null" />
        @if ($member?->user_id !== null)<x-app.text size="sm">{{ __('members.linked_email') }}</x-app.text>@endif
        <x-form.input name="phone" type="tel" :label="__('members.fields.phone')" :value="old('phone', $member?->phone)" autocomplete="tel" />
        <x-app.section :title="__('members.invoice.title')" :description="__('members.invoice.description')">
            <x-form.input name="invoice_company_name" :label="__('members.invoice.company_name')" :value="old('invoice_company_name', $member?->invoice_company_name)" data-brreg-field="invoice_company_name" />
            <x-form.input name="invoice_organization_number" :label="__('members.invoice.organization_number')" :value="old('invoice_organization_number', $member?->invoice_organization_number)" inputmode="numeric" autocomplete="off" data-brreg-organization-number />
            <x-app.button
                type="button"
                data-brreg-lookup
                data-brreg-lookup-url="{{ route('clubs.brreg-entities.show', [$club, 'ORGANIZATION_NUMBER']) }}"
                data-brreg-loading-label="{{ __('members.invoice.lookup_loading') }}"
                data-brreg-required-message="{{ __('members.invoice.lookup_required') }}"
                data-brreg-confirmation="{{ __('members.invoice.lookup_confirmation') }}"
            >{{ __('members.invoice.lookup') }}</x-app.button>
            <x-app.alert variant="danger" hidden data-brreg-error></x-app.alert>
            <x-form.textarea name="invoice_address" :label="__('members.invoice.address')" :value="old('invoice_address', $member?->invoice_address)" :rows="2" data-brreg-field="invoice_address" />
            <x-form.input name="invoice_postal_code" :label="__('members.invoice.postal_code')" :value="old('invoice_postal_code', $member?->invoice_postal_code)" data-brreg-field="invoice_postal_code" />
            <x-form.input name="invoice_city" :label="__('members.invoice.city')" :value="old('invoice_city', $member?->invoice_city)" data-brreg-field="invoice_city" />
        </x-app.section>
        <x-form.actions>
            <x-app.link-button :href="route('clubs.members.index', $club)" variant="ghost">{{ __('app.actions.cancel') }}</x-app.link-button>
            <x-app.button type="submit">{{ __('app.actions.save') }}</x-app.button>
        </x-form.actions>
    </x-form>
</x-app.card>
