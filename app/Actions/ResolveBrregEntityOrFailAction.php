<?php

namespace App\Actions;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Validation\ValidationException;

class ResolveBrregEntityOrFailAction
{
    public function __construct(private LookupBrregEntityAction $lookupBrregEntity) {}

    /**
     * @return array{invoice_company_name: ?string, invoice_address: ?string, invoice_postal_code: ?string, invoice_city: ?string}
     *
     * @throws ValidationException
     */
    public function handle(string $organizationNumber): array
    {
        try {
            $entity = $this->lookupBrregEntity->handle($organizationNumber);
        } catch (ConnectionException|RequestException) {
            throw ValidationException::withMessages([
                'organization_number' => __('brreg.lookup_unavailable'),
            ]);
        }

        if ($entity === null) {
            throw ValidationException::withMessages([
                'organization_number' => __('brreg.lookup_not_found'),
            ]);
        }

        return $entity;
    }
}
