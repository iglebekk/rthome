<x-layouts.app :title="__('invoices.invoice')" :$club>
    <x-app.page-header :title="$invoice->number ?? __('invoices.invoice')" :description="$invoice->recipient_name ?? $invoice->member?->name" :eyebrow="$club->name">
        <x-slot:actions>
            <div class="flex flex-wrap items-center justify-end gap-2">
                <x-app.link-button :href="route('clubs.invoices.index', $club)" variant="ghost" icon="arrow-left">{{ __('invoices.actions.overview') }}</x-app.link-button>
                <x-app.link-button :href="route('clubs.invoices.download', [$club, $invoice])" variant="filled" icon="arrow-down-tray" data-print-link>{{ __('invoices.actions.download') }}</x-app.link-button>
            @if (($invoice->document_type?->value ?? $invoice->document_type) === 'invoice' && ($invoice->status?->value ?? $invoice->status) !== 'credited')
                <x-app.dialog :name="'credit-invoice-'.$invoice->getKey()" :title="__('invoices.credit_title', ['number' => $invoice->number])" :description="__('invoices.credit_description')" :confirm-label="__('invoices.actions.credit')" :action="route('clubs.invoices.credit', [$club, $invoice])" method="POST">
                    <x-slot:trigger><x-app.button variant="danger">{{ __('invoices.actions.credit') }}</x-app.button></x-slot:trigger>
                </x-app.dialog>
            @endif
            </div>
        </x-slot:actions>
    </x-app.page-header>
    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_19rem] lg:items-start">
        <div class="grid gap-6">
            @if ($invoice->creditNote->isNotEmpty())
                <x-app.card class="border-l-4 border-l-amber-500 bg-amber-50/70 dark:bg-amber-950/20">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <x-app.text>{{ __('invoices.credited_by') }}</x-app.text>
                        <x-app.link-button :href="route('clubs.invoices.show', [$club, $invoice->creditNote->first()])" variant="filled">
                            {{ __('invoices.actions.view_credit_note', ['number' => $invoice->creditNote->first()->number]) }}
                        </x-app.link-button>
                    </div>
                </x-app.card>
            @endif
            @if ($invoice->creditedInvoice)
                <x-app.card class="border-l-4 border-l-sky-500 bg-sky-50/70 dark:bg-sky-950/20">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <x-app.text>{{ __('invoices.credits_invoice') }}</x-app.text>
                        <x-app.link-button :href="route('clubs.invoices.show', [$club, $invoice->creditedInvoice])" variant="filled">
                            {{ __('invoices.actions.view_invoice', ['number' => $invoice->creditedInvoice->number]) }}
                        </x-app.link-button>
                    </div>
                </x-app.card>
            @endif
            <x-app.card class="grid gap-6">
                <div class="flex flex-wrap items-start justify-between gap-3 border-b border-zinc-200 pb-5 dark:border-zinc-700">
                    <div class="grid gap-1">
                        <x-app.heading size="lg">{{ __('invoices.sections.details') }}</x-app.heading>
                        <x-app.text size="sm">{{ $invoice->recipient_name ?? $invoice->member?->name }}</x-app.text>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <x-app.badge>{{ __('invoices.statuses.'.($invoice->status?->value ?? $invoice->status ?? 'issued')) }}</x-app.badge>
                        @if ($invoice->paid_at)
                            <x-app.badge color="emerald">{{ __('invoices.statuses.paid') }}</x-app.badge>
                        @endif
                    </div>
                </div>
                <div class="grid gap-5 sm:grid-cols-3">
                    <div class="grid gap-1"><x-app.text size="sm">{{ __('invoices.fields.members') }}</x-app.text><x-app.heading>{{ $invoice->recipient_name ?? $invoice->member?->name }}</x-app.heading></div>
                    <div class="grid gap-1"><x-app.text size="sm">{{ __('invoices.fields.invoice_date') }}</x-app.text><x-app.heading>{{ $invoice->invoice_date?->translatedFormat('j. M Y') }}</x-app.heading></div>
                    <div class="grid gap-1 sm:text-right"><x-app.text size="sm">{{ __('invoices.fields.due_date') }}</x-app.text><x-app.heading>{{ $invoice->due_date?->translatedFormat('j. M Y') }}</x-app.heading></div>
                </div>
                <div class="grid gap-4 border-t border-zinc-200 pt-5 dark:border-zinc-700">
                    <div class="flex items-center justify-between gap-3">
                        <x-app.heading size="lg">{{ __('invoices.sections.lines') }}</x-app.heading>
                        <x-app.text size="sm">{{ $invoice->lines->count() }} {{ __('invoices.fields.lines') }}</x-app.text>
                    </div>
                    <div class="hidden grid-cols-[minmax(0,1fr)_auto_auto] gap-4 border-b border-zinc-200 pb-2 text-right dark:border-zinc-700 sm:grid">
                        <x-app.text size="sm" class="text-left">{{ __('invoices.fields.description') }}</x-app.text>
                        <x-app.text size="sm">{{ __('invoices.fields.quantity') }}</x-app.text>
                        <x-app.text size="sm">{{ __('invoices.fields.amount') }}</x-app.text>
                    </div>
                    <div class="grid gap-3">
                        @foreach ($invoice->lines as $line)
                            <div class="grid gap-1 border-b border-zinc-100 pb-3 last:border-0 last:pb-0 dark:border-zinc-800 sm:grid-cols-[minmax(0,1fr)_auto_auto] sm:items-center sm:gap-4">
                                <x-app.text>{{ $line->description }}</x-app.text>
                                <x-app.text size="sm">{{ $line->quantity }} × {{ number_format(($line->gross_unit_price_ore ?? 0) / 100, 2, '.', ' ') }}</x-app.text>
                                <x-app.heading size="lg" class="sm:text-right">{{ number_format(($line->gross_amount_ore ?? 0) / 100, 2, '.', ' ') }}</x-app.heading>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="flex items-end justify-between gap-4 border-t border-zinc-200 pt-5 dark:border-zinc-700">
                    <x-app.text>{{ __('invoices.fields.total') }}</x-app.text>
                    <x-app.heading size="xl">{{ number_format(($invoice->gross_total_ore ?? 0) / 100, 2, '.', ' ') }} kr</x-app.heading>
                </div>
            </x-app.card>
        </div>
        <aside class="grid content-start gap-6">
            <x-app.card class="grid gap-4">
                <div class="grid gap-1">
                    <x-app.heading size="lg">{{ __('invoices.sections.delivery') }}</x-app.heading>
                    <x-app.text size="sm">{{ $invoice->recipient_email }}</x-app.text>
                </div>
                <x-form :action="route('clubs.invoices.send', [$club, $invoice])" method="POST">
                    <x-app.button type="submit" class="w-full justify-center" icon="paper-airplane">{{ __('invoices.actions.send') }}</x-app.button>
                </x-form>
                @if ($invoice->email_sent_at)
                    <x-app.text size="sm">{{ __('invoices.email_last_sent', ['date' => $invoice->email_sent_at->locale(app()->getLocale())->translatedFormat('j. F Y H:i')]) }}</x-app.text>
                @endif
            </x-app.card>
            @if (($invoice->document_type?->value ?? $invoice->document_type) === 'invoice')
                <x-app.card class="grid gap-4">
                    <div class="grid gap-1">
                        <x-app.heading size="lg">{{ __('invoices.sections.payment') }}</x-app.heading>
                        <x-app.text size="sm">{{ __('invoices.account_number') }}: {{ $invoice->club_account_number ?? $club->account_number }}</x-app.text>
                        <x-app.text size="sm">{{ __($invoice->paid_at ? 'invoices.payment_recorded' : 'invoices.payment_pending') }}</x-app.text>
                    </div>
                    @if ($invoice->paid_at)
                        <x-app.alert variant="success">
                            {{ __('invoices.paid_on', ['date' => $invoice->paid_at->locale(app()->getLocale())->translatedFormat('j. F Y H:i'), 'amount' => number_format(($invoice->paid_amount_ore ?? $invoice->gross_total_ore) / 100, 2, ',', ' ').' kr']) }}
                        </x-app.alert>
                    @endif
                    @if (($invoice->status?->value ?? $invoice->status) !== 'credited')
                        <div class="grid gap-2">
                            <x-app.dialog
                                :name="'mark-invoice-paid-'.$invoice->getKey()"
                                :title="__('invoices.mark_paid_title')"
                                :description="__('invoices.mark_paid_description')"
                                :confirm-label="$invoice->paid_at ? __('invoices.actions.edit_payment') : __('invoices.actions.mark_paid')"
                                :action="route('clubs.invoices.mark-paid', [$club, $invoice])"
                                method="POST"
                                confirm-variant="filled"
                            >
                                <x-slot:trigger><x-app.button type="button" class="w-full justify-center" variant="{{ $invoice->paid_at ? 'ghost' : 'filled' }}" icon="check-circle">{{ $invoice->paid_at ? __('invoices.actions.edit_payment') : __('invoices.actions.mark_paid') }}</x-app.button></x-slot:trigger>
                                <x-slot:fields>
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <x-form.input name="paid_amount" type="number" step="0.01" min="0.01" :label="__('invoices.fields.paid_amount')" :value="number_format(($invoice->paid_amount_ore ?? $invoice->gross_total_ore) / 100, 2, '.', '')" required />
                                        <x-form.input name="paid_date" type="date" :label="__('invoices.fields.paid_date')" :value="$invoice->paid_at?->toDateString() ?? now()->toDateString()" required />
                                    </div>
                                </x-slot:fields>
                            </x-app.dialog>
                            @if ($invoice->paid_at)
                                <x-app.dialog
                                    :name="'unmark-invoice-paid-'.$invoice->getKey()"
                                    :title="__('invoices.undo_paid_title')"
                                    :description="__('invoices.undo_paid_description')"
                                    :confirm-label="__('invoices.actions.undo_paid')"
                                    :action="route('clubs.invoices.unmark-paid', [$club, $invoice])"
                                    method="POST"
                                >
                                    <x-slot:trigger><x-app.button type="button" class="w-full justify-center" variant="danger" icon="arrow-uturn-left">{{ __('invoices.actions.undo_paid') }}</x-app.button></x-slot:trigger>
                                </x-app.dialog>
                            @endif
                        </div>
                    @endif
                </x-app.card>
            @endif
        </aside>
    </div>
</x-layouts.app>
