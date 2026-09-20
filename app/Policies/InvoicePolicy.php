<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function view(User $user, Invoice $invoice): bool
    {
        return $user->clubs()->whereKey($invoice->club_id)->exists();
    }

    public function credit(User $user, Invoice $invoice): bool
    {
        return $this->view($user, $invoice);
    }

    public function markPaid(User $user, Invoice $invoice): bool
    {
        return $this->view($user, $invoice);
    }
}
