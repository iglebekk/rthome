<?php

use App\Models\Club;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(LazilyRefreshDatabase::class);

/** @return array{0: User, 1: Club} */
function organizationNumberContext(): array
{
    $user = User::factory()->create();
    $club = Club::factory()->create();
    Member::factory()->for($club)->for($user)->create();

    return [$user, $club];
}

test('confirming an organization number sets the club invoice name', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://data.brreg.no/enhetsregisteret/api/enheter/912345678' => Http::response(['navn' => 'Acme AS']),
    ]);
    [$user, $club] = organizationNumberContext();

    $this->actingAs($user)->put(route('clubs.organization-number.update', $club), [
        'organization_number' => '912 345 678',
    ])->assertRedirectToRoute('clubs.edit', $club);

    expect($club->refresh())
        ->organization_number->toBe('912345678')
        ->invoice_name->toBe('Acme AS');
});

test('confirming a changed organization number refreshes the invoice name', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://data.brreg.no/enhetsregisteret/api/enheter/912345678' => Http::response(['navn' => 'Original AS']),
        'https://data.brreg.no/enhetsregisteret/api/enheter/987654321' => Http::response(['navn' => 'Renamed AS']),
    ]);
    [$user, $club] = organizationNumberContext();

    $this->actingAs($user)->put(route('clubs.organization-number.update', $club), [
        'organization_number' => '912345678',
    ])->assertRedirect();

    expect($club->refresh()->invoice_name)->toBe('Original AS');

    $this->actingAs($user)->put(route('clubs.organization-number.update', $club), [
        'organization_number' => '987654321',
    ])->assertRedirect();

    expect($club->refresh()->invoice_name)->toBe('Renamed AS');

    Http::assertSentCount(2);
});

test('confirming the same unchanged organization number does not repeat the Brreg lookup', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://data.brreg.no/enhetsregisteret/api/enheter/912345678' => Http::response(['navn' => 'Acme AS']),
    ]);
    [$user, $club] = organizationNumberContext();

    $this->actingAs($user)->put(route('clubs.organization-number.update', $club), [
        'organization_number' => '912345678',
    ])->assertRedirect();

    $this->actingAs($user)->put(route('clubs.organization-number.update', $club), [
        'organization_number' => '912345678',
    ])->assertRedirect();

    expect($club->refresh()->invoice_name)->toBe('Acme AS');

    Http::assertSentCount(1);
});

test('an unresolvable organization number leaves the club unchanged and reports a validation error', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://data.brreg.no/enhetsregisteret/api/enheter/912345678' => Http::response([], 404),
    ]);
    [$user, $club] = organizationNumberContext();

    $this->actingAs($user)->put(route('clubs.organization-number.update', $club), [
        'organization_number' => '912345678',
    ])->assertSessionHasErrors('organization_number');

    expect($club->refresh())
        ->organization_number->toBeNull()
        ->invoice_name->toBeNull();
});

test('a Brreg outage leaves the club unchanged and reports a validation error', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://data.brreg.no/enhetsregisteret/api/enheter/912345678' => Http::response([], 503),
    ]);
    [$user, $club] = organizationNumberContext();

    $this->actingAs($user)->put(route('clubs.organization-number.update', $club), [
        'organization_number' => '912345678',
    ])->assertSessionHasErrors('organization_number');

    expect($club->refresh())
        ->organization_number->toBeNull()
        ->invoice_name->toBeNull();
});

test('a non-member cannot confirm an organization number for another club', function () {
    Http::preventStrayRequests();
    $outsider = User::factory()->create();
    [, $club] = organizationNumberContext();

    $this->actingAs($outsider)->put(route('clubs.organization-number.update', $club), [
        'organization_number' => '912345678',
    ])->assertNotFound();

    expect($club->refresh()->organization_number)->toBeNull();
});

test('the club settings form silently ignores an organization_number field', function () {
    [$user, $club] = organizationNumberContext();

    $this->actingAs($user)->put(route('clubs.update', $club), [
        'name' => $club->name,
        'organization_number' => '912345678',
    ])->assertRedirect();

    expect($club->refresh())
        ->organization_number->toBeNull()
        ->invoice_name->toBeNull();
});
