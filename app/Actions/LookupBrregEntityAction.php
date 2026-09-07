<?php

namespace App\Actions;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class LookupBrregEntityAction
{
    /**
     * @return array{invoice_company_name: ?string, invoice_address: ?string, invoice_postal_code: ?string, invoice_city: ?string}|null
     *
     * @throws ConnectionException
     */
    public function handle(string $organizationNumber): ?array
    {
        $response = Http::acceptJson()
            ->connectTimeout(3)
            ->timeout(5)
            ->get(config('services.brreg.base_url').'/enheter/'.$organizationNumber);

        if ($response->notFound() || $response->status() === 410) {
            return null;
        }

        $response->throw();

        $entity = $response->json();
        $address = Arr::get($entity, 'postadresse') ?? Arr::get($entity, 'forretningsadresse');

        return [
            'invoice_company_name' => Arr::get($entity, 'navn'),
            'invoice_address' => $this->addressLines(Arr::get($address, 'adresse')),
            'invoice_postal_code' => Arr::get($address, 'postnummer'),
            'invoice_city' => Arr::get($address, 'poststed'),
        ];
    }

    /**
     * @param  array<int, mixed>|null  $lines
     */
    private function addressLines(?array $lines): ?string
    {
        $address = collect($lines)
            ->filter(fn (mixed $line): bool => is_string($line) && $line !== '')
            ->join("\n");

        return $address === '' ? null : $address;
    }
}
