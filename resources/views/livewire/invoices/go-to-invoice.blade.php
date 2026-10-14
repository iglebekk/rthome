<x-app.section>
    <x-app.modal
        name="go-to-invoice"
        :title="__('invoices.go_to.title')"
        :trigger-label="__('invoices.go_to.open')"
        wire:close="resetLookup"
    >
        <x-form.input
            name="invoiceNumber"
            :label="__('invoices.go_to.number')"
            type="text"
            inputmode="numeric"
            autocomplete="off"
            wire:model.live.debounce.400ms="invoiceNumber"
        />

        <x-app.text size="sm" wire:loading wire:target="invoiceNumber" role="status" aria-live="polite">
            {{ __('invoices.go_to.searching') }}
        </x-app.text>

        <x-app.text size="sm" wire:loading.remove wire:target="invoiceNumber" role="status" aria-live="polite">
            @if (strlen($invoiceNumber) >= 3)
                {{ $invoiceExists ? __('invoices.go_to.found', ['number' => $invoiceNumber]) : __('invoices.go_to.not_found') }}
            @endif
        </x-app.text>

        <x-slot:actions>
            <x-app.button
                type="button"
                wire:click="goToInvoice"
                wire:loading.attr="disabled"
                wire:target="invoiceNumber,goToInvoice"
                x-bind:disabled="$wire.$dirty('invoiceNumber') || ! $wire.invoiceExists"
            >{{ __('invoices.go_to.go') }}</x-app.button>
        </x-slot:actions>
    </x-app.modal>
</x-app.section>
