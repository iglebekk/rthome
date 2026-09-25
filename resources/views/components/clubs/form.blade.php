@props(['club' => null])

<x-app.card>
    <x-form id="club-settings-form" :action="$club ? route('clubs.update', $club) : route('clubs.store')" :method="$club ? 'PUT' : 'POST'">
        <x-form.input name="name" :label="__('clubs.settings.name')" :value="old('name', $club?->name)" required autofocus />
        @if ($club)
            <x-form.select name="locale" :label="__('clubs.settings.locale')" :options="__('clubs.settings.locale_options')" :value="old('locale', $club->locale)" required />
        @else
            <x-app.section :title="__('clubs.settings.invoice_title')" :description="__('clubs.settings.invoice_description')">
                <x-form.input name="account_number" :label="__('clubs.settings.account_number')" :value="old('account_number')" inputmode="numeric" autocomplete="off" />
            </x-app.section>
        @endif
        <x-form.actions>
            @if ($club)<x-app.link-button :href="route('clubs.dashboard', $club)" variant="ghost">{{ __('app.actions.cancel') }}</x-app.link-button>@endif
            <x-app.button type="submit">{{ $club ? __('app.actions.save') : __('clubs.create.submit') }}</x-app.button>
        </x-form.actions>
    </x-form>
</x-app.card>

@if ($club)
    <x-app.card>
        <x-app.section :title="__('clubs.settings.invoice_title')" :description="__('clubs.settings.invoice_description')">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 sm:items-end">
                <div class="sm:col-span-2">
                    @if ($club->organization_number)
                        <x-form.input name="organization_number_display" :label="__('clubs.settings.organization_number')" :value="$club->organization_number" readonly />
                    @endif
                </div>
                <div class="sm:col-span-1">
                    <x-brreg.lookup-modal
                        class="w-full"
                        name="club-organization-number"
                        :trigger-label="$club->organization_number ? __('clubs.settings.organization_number_change') : __('clubs.settings.organization_number_add')"
                        :current-organization-number="$club->organization_number"
                        :lookup-url="route('clubs.brreg-entities.show', [$club, 'ORGANIZATION_NUMBER'])"
                        :confirm-url="route('clubs.organization-number.update', $club)"
                        :summary-fields="['invoice_company_name' => __('clubs.settings.invoice_name')]"
                    />
                </div>
            </div>
            @if ($club->invoice_name)
                <x-form.input name="invoice_name_display" :label="__('clubs.settings.invoice_name')" :value="$club->invoice_name" readonly />
                <x-app.text size="sm">{{ __('clubs.settings.invoice_name_hint') }}</x-app.text>
            @endif
            <x-form.input name="account_number" :label="__('clubs.settings.account_number')" :value="old('account_number', $club->account_number)" inputmode="numeric" autocomplete="off" form="club-settings-form" />
            <x-form.error name="organization_number" />
        </x-app.section>
    </x-app.card>
@endif
