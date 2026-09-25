<?php

namespace App\Actions;

use App\Models\Member;
use Illuminate\Validation\ValidationException;

class SyncMemberInvoiceDetailsAction
{
    public function __construct(private ResolveBrregEntityOrFailAction $resolveBrregEntity) {}

    /**
     * @throws ValidationException
     */
    public function handle(Member $member, string $organizationNumber): Member
    {
        if ($member->invoice_organization_number === $organizationNumber && filled($member->invoice_company_name)) {
            return $member;
        }

        $entity = $this->resolveBrregEntity->handle($organizationNumber);

        $member->update([
            'invoice_organization_number' => $organizationNumber,
            'invoice_company_name' => $entity['invoice_company_name'],
            'invoice_address' => $entity['invoice_address'],
            'invoice_postal_code' => $entity['invoice_postal_code'],
            'invoice_city' => $entity['invoice_city'],
        ]);

        return $member;
    }
}
