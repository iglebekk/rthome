<?php

use App\Models\Club;
use App\Models\Event;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

/** @return array{0: User, 1: Club, 2: Member} */
function createAdministrationContext(): array
{
    $user = User::factory()->create();
    $club = Club::factory()->create();
    $member = Member::factory()->for($club)->for($user)->create();

    return [$user, $club, $member];
}

test('a club member can add and update a member', function () {
    [$user, $club] = createAdministrationContext();

    $this->actingAs($user)->post(route('clubs.members.store', $club), [
        'name' => 'New Member',
        'email' => 'New.Member@example.com',
        'phone' => '+47 999 99 999',
        'invoice_company_name' => 'Acme AS',
        'invoice_organization_number' => '987 654 321',
        'invoice_address' => 'Invoice Street 1',
        'invoice_postal_code' => '0150',
        'invoice_city' => 'Oslo',
    ])->assertRedirectToRoute('clubs.members.index', $club);

    $member = $club->members()->where('email', 'new.member@example.com')->sole();

    $this->actingAs($user)->put(route('clubs.members.update', [$club, $member]), [
        'name' => 'Updated Member',
        'email' => 'new.member@example.com',
        'phone' => null,
        'invoice_company_name' => 'Updated Acme AS',
        'invoice_organization_number' => '987654321',
        'invoice_address' => 'Invoice Street 2',
        'invoice_postal_code' => '5003',
        'invoice_city' => 'Bergen',
    ])->assertRedirectToRoute('clubs.members.index', $club);

    expect($member->refresh()->name)->toBe('Updated Member')
        ->and($member->phone)->toBeNull()
        ->and($member->invoice_company_name)->toBe('Updated Acme AS')
        ->and($member->invoice_organization_number)->toBe('987654321')
        ->and($member->invoice_address)->toBe('Invoice Street 2')
        ->and($member->invoice_postal_code)->toBe('5003')
        ->and($member->invoice_city)->toBe('Bergen');
});

test('member writes validate input and hide clubs outside the current user memberships', function () {
    [$user, $club] = createAdministrationContext();
    $otherClub = Club::factory()->create();

    $this->actingAs($user)->post(route('clubs.members.store', $club), [
        'name' => null,
        'email' => 'invalid',
        'invoice_organization_number' => '123',
    ])->assertSessionHasErrors(['name', 'email', 'invoice_organization_number']);

    $this->actingAs($user)->post(route('clubs.members.store', $otherClub), [
        'name' => 'Hidden',
    ])->assertNotFound();
});

test('invoice settings are optional when adding a member', function () {
    [$user, $club] = createAdministrationContext();

    $this->actingAs($user)
        ->post(route('clubs.members.store', $club), ['name' => 'Member without invoice details'])
        ->assertRedirectToRoute('clubs.members.index', $club);

    $member = $club->members()->where('name', 'Member without invoice details')->sole();

    expect($member->invoice_company_name)->toBeNull()
        ->and($member->invoice_organization_number)->toBeNull()
        ->and($member->invoice_address)->toBeNull()
        ->and($member->invoice_postal_code)->toBeNull()
        ->and($member->invoice_city)->toBeNull();
});

test('the member form renders invoice settings and the Brreg lookup control', function () {
    [$user, $club] = createAdministrationContext();

    $this->actingAs($user)
        ->get(route('clubs.members.create', $club))
        ->assertSee(__('members.invoice.title'))
        ->assertSee(__('members.invoice.description'))
        ->assertSee(__('members.invoice.lookup'))
        ->assertSee(route('clubs.brreg-entities.show', [$club, 'ORGANIZATION_NUMBER']), false);
});

test('the member list shows the invoice company name when it exists', function () {
    [$user, $club] = createAdministrationContext();
    Member::factory()->for($club)->create([
        'name' => 'Invoice Contact',
        'invoice_company_name' => 'Invoice Recipient AS',
    ]);

    $this->actingAs($user)
        ->get(route('clubs.members.index', $club))
        ->assertSee('Invoice Recipient AS');
});

test('an activated member email cannot be edited from club administration', function () {
    [$user, $club, $member] = createAdministrationContext();
    $originalEmail = $member->email;

    $this->actingAs($user)->put(route('clubs.members.update', [$club, $member]), [
        'name' => $member->name,
        'email' => 'changed@example.com',
    ])->assertSessionHasErrors('email');

    expect($member->refresh()->email)->toBe($originalEmail);
});

test('club deletion requires the exact club name', function () {
    [$user, $club] = createAdministrationContext();

    $this->actingAs($user)->delete(route('clubs.destroy', $club), [
        'club_name' => 'Wrong name',
    ])->assertSessionHasErrors('club_name');
    $this->assertModelExists($club);

    $this->actingAs($user)->delete(route('clubs.destroy', $club), [
        'club_name' => $club->name,
    ])->assertRedirectToRoute('home');
    $this->assertModelMissing($club);
});

test('web event creation accepts local date fields and stores an uploaded image', function () {
    Storage::fake('public');
    [$user, $club] = createAdministrationContext();

    $this->actingAs($user)->post(route('clubs.events.store', $club), [
        'name' => 'Summer gathering',
        'starts_at' => '2030-06-01T18:00',
        'ends_at' => '2030-06-01T20:00',
        'image' => UploadedFile::fake()->image('summer.jpg'),
    ])->assertRedirectToRoute('clubs.events.index', $club);

    $event = $club->events()->sole();
    Storage::disk('public')->assertExists($event->image_path);
});

test('web event updates redirect to the event view', function () {
    [$user, $club] = createAdministrationContext();
    $event = Event::factory()->for($club)->create();

    $this->actingAs($user)->put(route('clubs.events.update', [$club, $event]), [
        'name' => 'Updated event',
        'starts_at' => '2030-06-01T18:00',
        'ends_at' => '2030-06-01T20:00',
    ])->assertRedirectToRoute('clubs.events.show', [$club, $event]);
});
