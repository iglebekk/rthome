<?php

namespace App\Policies;

use App\Enums\InvoiceCreationStatus;
use App\Models\Club;
use App\Models\InvoiceCreation;
use App\Models\User;

class InvoiceCreationPolicy
{
    public function view(User $user, InvoiceCreation $invoiceCreation): bool
    {
        return $user->clubs()->whereKey($invoiceCreation->club_id)->exists();
    }

    public function create(User $user, Club $club): bool
    {
        return $user->clubs()->whereKey($club->getKey())->exists();
    }

    public function update(User $user, InvoiceCreation $invoiceCreation): bool
    {
        return $this->view($user, $invoiceCreation)
            && $invoiceCreation->status === InvoiceCreationStatus::Draft;
    }

    public function delete(User $user, InvoiceCreation $invoiceCreation): bool
    {
        return $this->update($user, $invoiceCreation);
    }
}
