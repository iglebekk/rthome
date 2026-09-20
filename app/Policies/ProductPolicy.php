<?php

namespace App\Policies;

use App\Enums\InvoiceCreationStatus;
use App\Models\Club;
use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user, Club $club): bool
    {
        return $user->clubs()->whereKey($club->getKey())->exists();
    }

    public function view(User $user, Product $product): bool
    {
        return $user->clubs()->whereKey($product->club_id)->exists();
    }

    public function create(User $user, Club $club): bool
    {
        return $this->viewAny($user, $club);
    }

    public function update(User $user, Product $product): bool
    {
        return $this->view($user, $product);
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->view($user, $product)
            && ! $product->invoiceCreationLines()
                ->whereHas('creation', fn ($query) => $query->where('status', InvoiceCreationStatus::Draft))
                ->exists();
    }
}
