<p>{{ __('invoices.mail.greeting', ['name' => $invoice->recipient_name]) }}</p>
<p>{{ __('invoices.mail.body', ['number' => $invoice->number]) }}</p>
<p>{{ __('invoices.mail.footer') }}</p>
