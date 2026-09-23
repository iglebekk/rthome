@props(['name', 'triggerLabel', 'currentOrganizationNumber' => null, 'lookupUrl', 'confirmUrl', 'summaryFields'])

<x-app.modal :$name :title="__('brreg.modal.title')" :trigger-label="$triggerLabel" {{ $attributes }}>
    <div
        data-brreg-modal-content="{{ $name }}"
        data-brreg-lookup-url="{{ $lookupUrl }}"
        data-brreg-current-organization-number="{{ $currentOrganizationNumber }}"
        data-brreg-loading-label="{{ __('brreg.modal.fetch_loading') }}"
        data-brreg-required-message="{{ __('brreg.modal.required') }}"
        data-brreg-unavailable-message="{{ __('brreg.lookup_unavailable') }}"
        class="grid gap-4"
    >
        <div data-brreg-panel="input" class="grid gap-4">
            <x-form.input
                name="organization_number_preview"
                :label="__('brreg.modal.organization_number_label')"
                :value="$currentOrganizationNumber"
                inputmode="numeric"
                autocomplete="off"
                data-brreg-organization-number
            />
            <x-app.alert variant="danger" hidden data-brreg-error></x-app.alert>
            <x-form.actions>
                <x-app.button type="button" data-brreg-fetch>{{ __('brreg.modal.fetch') }}</x-app.button>
            </x-form.actions>
        </div>

        <div data-brreg-panel="confirm" hidden class="grid gap-4">
            <dl class="grid gap-2">
                @foreach ($summaryFields as $field => $label)
                    <div>
                        <dt class="text-sm font-medium">{{ $label }}</dt>
                        <dd data-brreg-summary-value="{{ $field }}"></dd>
                    </div>
                @endforeach
            </dl>
            <x-form :action="$confirmUrl" method="PUT">
                <x-form.hidden name="organization_number" value="" data-brreg-confirm-organization-number />
                <x-form.actions>
                    <x-app.button type="button" variant="ghost" data-brreg-back>{{ __('brreg.modal.back') }}</x-app.button>
                    <x-app.button type="submit">{{ __('brreg.modal.confirm') }}</x-app.button>
                </x-form.actions>
            </x-form>
        </div>
    </div>
</x-app.modal>
