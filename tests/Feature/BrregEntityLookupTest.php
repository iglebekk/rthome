<?php

use App\Models\Club;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(LazilyRefreshDatabase::class);

/** @return array{0: User, 1: Club} */
function createBrregLookupContext(): array
{
    $user = User::factory()->create();
    $club = Club::factory()->create();

    Member::factory()->for($club)->for($user)->create();

    return [$user, $club];
}

test('returns invoice details from the postal address for a club member', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://data.brreg.no/enhetsregisteret/api/enheter/987654321' => Http::response([
            'navn' => 'Acme AS',
            'postadresse' => [
                'adresse' => ['Postboks 1'],
                'postnummer' => '0150',
                'poststed' => 'Oslo',
            ],
            'forretningsadresse' => [
                'adresse' => ['Office Street 1'],
                'postnummer' => '5003',
                'poststed' => 'Bergen',
            ],
        ]),
    ]);
    [$user, $club] = createBrregLookupContext();

    $this->actingAs($user)
        ->getJson(route('clubs.brreg-entities.show', [$club, '987 654 321']))
        ->assertOk()
        ->assertJsonPath('data.invoice_company_name', 'Acme AS')
        ->assertJsonPath('data.invoice_address', 'Postboks 1')
        ->assertJsonPath('data.invoice_postal_code', '0150')
        ->assertJsonPath('data.invoice_city', 'Oslo');

    Http::assertSent(fn ($request): bool => $request->url() === 'https://data.brreg.no/enhetsregisteret/api/enheter/987654321');
});

test('falls back to the business address when no postal address exists', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://data.brreg.no/enhetsregisteret/api/enheter/987654321' => Http::response([
            'navn' => 'Acme AS',
            'forretningsadresse' => [
                'adresse' => ['Office Street 1', 'Floor 2'],
                'postnummer' => '5003',
                'poststed' => 'Bergen',
            ],
        ]),
    ]);
    [$user, $club] = createBrregLookupContext();

    $this->actingAs($user)
        ->getJson(route('clubs.brreg-entities.show', [$club, '987654321']))
        ->assertOk()
        ->assertJsonPath('data.invoice_address', "Office Street 1\nFloor 2")
        ->assertJsonPath('data.invoice_postal_code', '5003')
        ->assertJsonPath('data.invoice_city', 'Bergen');
});

test('returns 422 for an invalid organization number', function () {
    [$user, $club] = createBrregLookupContext();

    $this->actingAs($user)
        ->getJson(route('clubs.brreg-entities.show', [$club, '123']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('organization_number');
});

test('returns 404 when Brreg cannot find the organization', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://data.brreg.no/enhetsregisteret/api/enheter/987654321' => Http::response([], 404),
    ]);
    [$user, $club] = createBrregLookupContext();

    $this->actingAs($user)
        ->getJson(route('clubs.brreg-entities.show', [$club, '987654321']))
        ->assertNotFound()
        ->assertJsonPath('message', __('brreg.lookup_not_found'));
});

test('returns 503 when Brreg is unavailable', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://data.brreg.no/enhetsregisteret/api/enheter/987654321' => Http::response([], 503),
    ]);
    [$user, $club] = createBrregLookupContext();

    $this->actingAs($user)
        ->getJson(route('clubs.brreg-entities.show', [$club, '987654321']))
        ->assertServiceUnavailable()
        ->assertJsonPath('message', __('brreg.lookup_unavailable'));
});

test('returns 404 when the user does not belong to the club', function () {
    Http::preventStrayRequests();
    $user = User::factory()->create();
    $club = Club::factory()->create();

    $this->actingAs($user)
        ->getJson(route('clubs.brreg-entities.show', [$club, '987654321']))
        ->assertNotFound();
});

test('a club member can use the lookup for the club itself, without ever having created a member', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://data.brreg.no/enhetsregisteret/api/enheter/987654321' => Http::response(['navn' => 'Acme AS']),
    ]);
    $user = User::factory()->create();
    $club = Club::factory()->create();
    Member::factory()->for($club)->for($user)->create();

    // Documents that authorization is based on club membership (ClubPolicy::update), not on the
    // ability to create a Member — this endpoint is now also used by the club's own org-number modal.
    $this->actingAs($user)
        ->getJson(route('clubs.brreg-entities.show', [$club, '987654321']))
        ->assertOk();
});
