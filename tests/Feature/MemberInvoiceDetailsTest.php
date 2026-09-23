<?php

use App\Models\Club;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(LazilyRefreshDatabase::class);

/** @return array{0: User, 1: Club, 2: Member} */
function memberInvoiceDetailsContext(): array
{
    $user = User::factory()->create();
    $club = Club::factory()->create();
    $member = Member::factory()->for($club)->for($user)->create();

    return [$user, $club, $member];
}

test('confirming an organization number sets the member invoice details', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://data.brreg.no/enhetsregisteret/api/enheter/987654321' => Http::response([
            'navn' => 'Acme AS',
            'postadresse' => [
                'adresse' => ['Postboks 1'],
                'postnummer' => '0150',
                'poststed' => 'Oslo',
            ],
        ]),
    ]);
    [$user, $club, $member] = memberInvoiceDetailsContext();

    $this->actingAs($user)->put(route('clubs.members.invoice-details.update', [$club, $member]), [
        'organization_number' => '987 654 321',
    ])->assertRedirectToRoute('clubs.members.edit', [$club, $member]);

    expect($member->refresh())
        ->invoice_organization_number->toBe('987654321')
        ->invoice_company_name->toBe('Acme AS')
        ->invoice_address->toBe('Postboks 1')
        ->invoice_postal_code->toBe('0150')
        ->invoice_city->toBe('Oslo');
});

test('confirming the same unchanged organization number does not repeat the Brreg lookup', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://data.brreg.no/enhetsregisteret/api/enheter/987654321' => Http::response(['navn' => 'Acme AS']),
    ]);
    [$user, $club, $member] = memberInvoiceDetailsContext();

    $this->actingAs($user)->put(route('clubs.members.invoice-details.update', [$club, $member]), [
        'organization_number' => '987654321',
    ])->assertRedirect();

    $this->actingAs($user)->put(route('clubs.members.invoice-details.update', [$club, $member]), [
        'organization_number' => '987654321',
    ])->assertRedirect();

    expect($member->refresh()->invoice_company_name)->toBe('Acme AS');

    Http::assertSentCount(1);
});

test('an unresolvable organization number leaves the member unchanged and reports a validation error', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://data.brreg.no/enhetsregisteret/api/enheter/987654321' => Http::response([], 404),
    ]);
    [$user, $club, $member] = memberInvoiceDetailsContext();

    $this->actingAs($user)->put(route('clubs.members.invoice-details.update', [$club, $member]), [
        'organization_number' => '987654321',
    ])->assertSessionHasErrors('organization_number');

    expect($member->refresh()->invoice_company_name)->toBeNull();
});

test('a Brreg outage leaves the member unchanged and reports a validation error', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://data.brreg.no/enhetsregisteret/api/enheter/987654321' => Http::response([], 503),
    ]);
    [$user, $club, $member] = memberInvoiceDetailsContext();

    $this->actingAs($user)->put(route('clubs.members.invoice-details.update', [$club, $member]), [
        'organization_number' => '987654321',
    ])->assertSessionHasErrors('organization_number');

    expect($member->refresh()->invoice_company_name)->toBeNull();
});

test('an outsider cannot confirm invoice details for another club member', function () {
    Http::preventStrayRequests();
    $outsider = User::factory()->create();
    [, $club, $member] = memberInvoiceDetailsContext();

    $this->actingAs($outsider)->put(route('clubs.members.invoice-details.update', [$club, $member]), [
        'organization_number' => '987654321',
    ])->assertNotFound();

    expect($member->refresh()->invoice_company_name)->toBeNull();
});
