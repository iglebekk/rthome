@props(['club', 'invoices', 'showSend' => false])

<div x-data="{ selected: [] }" class="grid gap-5" data-test="invoice-bulk-actions">
    @if ($invoices->isNotEmpty())
        <x-form id="invoice-bulk-form" :action="route('clubs.invoices.exports.store', $club)" method="POST" class="hidden" />

        <div class="flex flex-wrap items-center justify-between gap-3">
            <x-form.invoice-selection-checkbox
                name="select_all_invoices"
                :label="__('invoices.bulk.select_all')"
                value="all"
                x-on:change="selected = $event.target.checked ? Array.from($root.querySelectorAll('[data-invoice-selection]')).map((input) => input.value) : []"
                x-bind:checked="selected.length === Number($el.dataset.invoiceCount)"
                data-invoice-count="{{ $invoices->count() }}"
                data-test="invoice-select-all"
            />
            <div class="flex items-center gap-3" x-show="selected.length > 0" x-cloak>
                <x-app.text size="sm" x-text="$el.dataset.selectedLabel.replace(':count', selected.length)" data-selected-label="{{ __('invoices.bulk.selected', ['count' => ':count']) }}" data-test="invoice-selected-count"></x-app.text>
                <x-app.button
                    type="submit"
                    form="invoice-bulk-form"
                    formaction="{{ route('clubs.invoices.bulk-send', $club) }}"
                    variant="ghost"
                    icon="paper-airplane"
                    x-on:click="window.confirm($el.dataset.confirmTemplate.replace(':count', selected.length)) || $event.preventDefault()"
                    data-confirm-template="{{ __('invoices.bulk.send_confirm', ['count' => ':count']) }}"
                    data-test="invoice-bulk-send"
                >{{ __('invoices.actions.send_selected') }}</x-app.button>
                <x-app.button type="submit" form="invoice-bulk-form" formaction="{{ route('clubs.invoices.exports.store', $club) }}" variant="ghost" icon="archive-box-arrow-down" data-test="invoice-bulk-export">{{ __('invoices.actions.export_selected') }}</x-app.button>
            </div>
        </div>
    @endif

    @forelse ($invoices as $invoice)
        <x-app.card class="grid gap-4 sm:grid-cols-[1fr_auto] sm:items-center">
            <div class="flex items-start gap-3">
                <x-form.invoice-selection-checkbox
                    name="invoice_ids[]"
                    :label="__('invoices.invoice')"
                    :value="$invoice->getKey()"
                    x-model="selected"
                    form="invoice-bulk-form"
                    data-invoice-selection
                    data-test="invoice-select-{{ $invoice->getKey() }}"
                />
                <div class="grid gap-2">
                <div class="flex flex-wrap items-center gap-2"><x-app.heading>{{ $invoice->number ?? __('invoices.invoice') }}</x-app.heading><x-app.badge>{{ __('invoices.statuses.'.($invoice->status?->value ?? $invoice->status ?? 'issued')) }}</x-app.badge>@if ($invoice->paid_at)<x-app.badge color="emerald">{{ __('invoices.statuses.paid') }}</x-app.badge>@endif</div>
                <x-app.text size="sm">{{ $invoice->recipient_name ?? $invoice->member?->name }} · {{ number_format(($invoice->gross_total_ore ?? 0) / 100, 2, '.', ' ') }}</x-app.text>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <x-app.link-button :href="route('clubs.invoices.show', [$club, $invoice])" variant="filled">{{ __('invoices.actions.view') }}</x-app.link-button>
                <x-app.link-button :href="route('clubs.invoices.download', [$club, $invoice])" variant="ghost" icon="arrow-down-tray" data-print-link>{{ __('invoices.actions.download') }}</x-app.link-button>
                @if ($showSend)
                    <x-form :action="route('clubs.invoices.send', [$club, $invoice])" method="POST">
                        <x-app.button type="submit" variant="ghost" icon="paper-airplane">{{ __('invoices.actions.send') }}</x-app.button>
                    </x-form>
                @endif
            </div>
        </x-app.card>
    @empty
        <x-app.empty-state :title="__('invoices.empty')" :description="__('invoices.empty_description')" icon="document-text">
            <x-slot:action>
                <x-app.link-button :href="route('clubs.invoice-creations.create', $club)" icon="plus">{{ __('invoices.actions.create_one') }}</x-app.link-button>
            </x-slot:action>
        </x-app.empty-state>
    @endforelse
</div>
