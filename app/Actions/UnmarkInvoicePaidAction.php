<?php

namespace App\Actions;

use App\Models\Invoice;

class UnmarkInvoicePaidAction
{
    public function handle(Invoice $invoice): Invoice
    {
        $invoice->forceFill([
            'paid_at' => null,
            'paid_amount_ore' => null,
        ])->saveQuietly();

        return $invoice->refresh();
    }
}
