@props(['club', 'member' => null])

<x-app.card>
    <x-form :action="$member ? route('clubs.members.update', [$club, $member]) : route('clubs.members.store', $club)" :method="$member ? 'PUT' : 'POST'">
        <x-form.input name="name" :label="__('members.fields.name')" :value="old('name', $member?->name)" required autofocus />
        <x-form.input name="email" type="email" :label="__('members.fields.email')" :value="old('email', $member?->email)" :readonly="$member?->user_id !== null" />
        @if ($member?->user_id !== null)<x-app.text size="sm">{{ __('members.linked_email') }}</x-app.text>@endif
        <x-form.input name="phone" type="tel" :label="__('members.fields.phone')" :value="old('phone', $member?->phone)" autocomplete="tel" />
        <x-form.actions>
            <x-app.link-button :href="route('clubs.members.index', $club)" variant="ghost">{{ __('app.actions.cancel') }}</x-app.link-button>
            <x-app.button type="submit">{{ __('app.actions.save') }}</x-app.button>
        </x-form.actions>
    </x-form>
</x-app.card>

@if ($member)
    <x-app.card>
        <x-app.section :title="__('members.invoice.title')" :description="__('members.invoice.description')">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 sm:items-end">
                <div class="sm:col-span-2">
                    @if ($member->invoice_organization_number)
                        <x-form.input name="invoice_organization_number_display" :label="__('members.invoice.organization_number')" :value="$member->invoice_organization_number" readonly />
                    @endif
                </div>
                <div class="sm:col-span-1">
                    <x-brreg.lookup-modal
                        class="w-full"
                        name="member-invoice-details"
                        :trigger-label="$member->invoice_organization_number ? __('members.invoice.organization_number_change') : __('members.invoice.organization_number_add')"
                        :current-organization-number="$member->invoice_organization_number"
                        :lookup-url="route('clubs.brreg-entities.show', [$club, 'ORGANIZATION_NUMBER'])"
                        :confirm-url="route('clubs.members.invoice-details.update', [$club, $member])"
                        :summary-fields="[
                            'invoice_company_name' => __('members.invoice.company_name'),
                            'invoice_address' => __('members.invoice.address'),
                            'invoice_postal_code' => __('members.invoice.postal_code'),
                            'invoice_city' => __('members.invoice.city'),
                        ]"
                    />
                </div>
            </div>
            @if ($member->invoice_company_name)
                <x-app.text>{{ $member->invoice_company_name }}</x-app.text>
                <x-app.text size="sm">{{ $member->invoice_address }}</x-app.text>
                <x-app.text size="sm">{{ $member->invoice_postal_code }} {{ $member->invoice_city }}</x-app.text>
            @endif
            <x-form.error name="organization_number" />
        </x-app.section>
    </x-app.card>
@endif
