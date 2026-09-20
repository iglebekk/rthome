<?php

namespace App\Actions;

use App\Models\Invoice;
use Illuminate\Support\Carbon;

class MarkInvoicePaidAction
{
    public function handle(Invoice $invoice, int $paidAmountOre, Carbon $paidAt): Invoice
    {
        $invoice->forceFill([
            'paid_at' => $paidAt,
            'paid_amount_ore' => $paidAmountOre,
        ])->saveQuietly();

        return $invoice->refresh();
    }
}
