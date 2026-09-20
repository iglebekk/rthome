@props(['club', 'invoices', 'showSend' => false])

<div class="grid gap-5">
    @forelse ($invoices as $invoice)
        <x-app.card class="grid gap-4 sm:grid-cols-[1fr_auto] sm:items-center">
            <div class="grid gap-2">
                <div class="flex flex-wrap items-center gap-2"><x-app.heading>{{ $invoice->number ?? __('invoices.invoice') }}</x-app.heading><x-app.badge>{{ __('invoices.statuses.'.($invoice->status?->value ?? $invoice->status ?? 'issued')) }}</x-app.badge>@if ($invoice->paid_at)<x-app.badge color="emerald">{{ __('invoices.statuses.paid') }}</x-app.badge>@endif</div>
                <x-app.text size="sm">{{ $invoice->recipient_name ?? $invoice->member?->name }} · {{ number_format(($invoice->gross_total_ore ?? 0) / 100, 2, '.', ' ') }}</x-app.text>
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
