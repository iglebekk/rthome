@props(['document', 'returnUrl' => null, 'printOnLoad' => false])

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <title>{{ __('invoices.document_filename', ['number' => $document['number']]) }}</title>
        @if ($printOnLoad)
            @vite('resources/js/app.js')
        @endif
        <style>
            @page { margin: 18mm 18mm 20mm; }
            * { box-sizing: border-box; }
            body { color: #20252b; font-family: Arial, sans-serif; font-size: 10.5px; line-height: 1.45; margin: 0; }
            h1 { color: #111827; font-size: 28px; letter-spacing: .08em; margin: 0 0 7px; text-transform: uppercase; }
            h2 { color: #5f6872; font-size: 9px; letter-spacing: .12em; margin: 0 0 7px; text-transform: uppercase; }
            p { margin: 2px 0; }
            .muted, .label { color: #69737d; }
            .header, .parties, .summary { display: table; table-layout: fixed; width: 100%; }
            .header { border-bottom: 1.5px solid #1f2937; padding-bottom: 16px; }
            .header > div, .party, .payment, .totals { display: table-cell; vertical-align: top; }
            .seller { width: 58%; }
            .seller-name { font-size: 16px; font-weight: 700; margin-bottom: 5px; }
            .meta { text-align: right; width: 42%; }
            .meta p { margin: 3px 0; }
            .meta strong { color: #111827; }
            .parties { padding: 25px 0 29px; }
            .party { width: 50%; }
            .party + .party { border-left: 1px solid #dfe3e6; padding-left: 28px; }
            .party p strong { color: #111827; }
            table { border-collapse: collapse; width: 100%; }
            th { border-bottom: 1.5px solid #1f2937; color: #5f6872; font-size: 8.5px; font-weight: 700; padding: 0 6px 7px; text-align: left; text-transform: uppercase; }
            td { border-bottom: 1px solid #e5e7eb; padding: 9px 6px; vertical-align: top; }
            .number { text-align: right; white-space: nowrap; }
            .summary { border-top: 1.5px solid #1f2937; margin-top: 21px; padding-top: 14px; }
            .payment { width: 58%; }
            .totals { padding-left: 36px; width: 42%; }
            .total-row { display: table; padding: 3px 0; width: 100%; }
            .total-row > span { display: table-cell; }
            .total-row > span:last-child { text-align: right; }
            .total-row.final { border-top: 1px solid #cdd2d7; color: #111827; font-size: 15px; font-weight: 700; margin-top: 5px; padding-top: 9px; }
            .footer { border-top: 1px solid #e5e7eb; color: #69737d; margin-top: 38px; padding-top: 10px; }
            @media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
        </style>
    </head>
    <body @if ($printOnLoad) data-print-on-load @endif @if ($returnUrl) data-print-return-url="{{ $returnUrl }}" @endif>
        <div class="header">
            <div class="seller">
                <p class="seller-name">{{ $document['seller_name'] }}</p>
                <p>{{ __('invoices.organization_number') }}: {{ $document['seller_organization_number'] }}</p>
            </div>
            <div class="meta">
                <h1>{{ $document['document_type'] }}</h1>
                <p><strong>{{ __('invoices.number') }} {{ $document['number'] }}</strong></p>
                <p>{{ __('invoices.invoice_date') }}: {{ $document['invoice_date'] }}</p>
                <p>{{ __('invoices.due_date') }}: {{ $document['due_date'] }}</p>
            </div>
        </div>

        <div class="parties">
            <div class="party">
                <h2>{{ __('invoices.return_address') }}</h2>
                <p><strong>{{ $document['seller_name'] }}</strong></p>
                <p>{{ __('invoices.organization_number') }}: {{ $document['seller_organization_number'] }}</p>
            </div>
            <div class="party">
                <h2>{{ __('invoices.to') }}</h2>
                <p><strong>{{ $document['recipient_name'] }}</strong></p>
                @if ($document['recipient_company_name'] && $document['recipient_company_name'] !== $document['recipient_name'])
                    <p>{{ $document['recipient_company_name'] }}</p>
                @endif
                @if ($document['recipient_organization_number'])
                    <p>{{ $document['recipient_organization_number'] }}</p>
                @endif
                @if ($document['recipient_address'])
                    <p>{{ $document['recipient_address'] }}</p>
                @endif
                @if ($document['recipient_postal_code'] || $document['recipient_city'])
                    <p>{{ $document['recipient_postal_code'] }} {{ $document['recipient_city'] }}</p>
                @endif
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>{{ __('invoices.description') }}</th>
                    <th>{{ __('invoices.vat') }}</th>
                    <th class="number">{{ __('invoices.quantity') }}</th>
                    <th class="number">{{ __('invoices.unit_price') }}</th>
                    <th class="number">{{ __('invoices.amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($document['lines'] as $line)
                    <tr>
                        <td>{{ $line['description'] }}</td>
                        <td>{{ $line['vat_treatment'] }}</td>
                        <td class="number">{{ $line['quantity'] }}</td>
                        <td class="number">{{ $line['unit_price'] }}</td>
                        <td class="number">{{ $line['gross_amount'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <div class="payment">
                <h2>{{ __('invoices.payment') }}</h2>
                <p>{{ __('invoices.account_number') }}: {{ $document['seller_account_number'] }}</p>
                <p>{{ __('invoices.due_date') }}: {{ $document['due_date'] }}</p>
                <p>{{ __('invoices.payment_reference', ['number' => $document['number']]) }}</p>
            </div>
            <div class="totals">
                <div class="total-row"><span class="label">{{ __('invoices.net_total') }}</span><span>{{ $document['net_total'] }}</span></div>
                <div class="total-row"><span class="label">{{ __('invoices.vat_total') }}</span><span>{{ $document['vat_total'] }}</span></div>
                <div class="total-row final"><span>{{ __('invoices.total') }}</span><span>{{ $document['gross_total'] }}</span></div>
            </div>
        </div>

        <p class="footer">{{ __('invoices.payment_terms') }}</p>
    </body>
</html>
