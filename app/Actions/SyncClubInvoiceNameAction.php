<?php

namespace App\Actions;

use App\Models\Club;
use Illuminate\Validation\ValidationException;

class SyncClubInvoiceNameAction
{
    public function __construct(private ResolveBrregEntityOrFailAction $resolveBrregEntity) {}

    /**
     * @throws ValidationException
     */
    public function handle(Club $club, string $organizationNumber): Club
    {
        if ($club->organization_number === $organizationNumber && filled($club->invoice_name)) {
            return $club;
        }

        $entity = $this->resolveBrregEntity->handle($organizationNumber);

        $club->update([
            'organization_number' => $organizationNumber,
            'invoice_name' => $entity['invoice_company_name'],
        ]);

        return $club;
    }
}
