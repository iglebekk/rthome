<?php

use App\Models\Club;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

/**
 * @return array{0: User, 1: Club}
 */
function createMemberImportContext(): array
{
    $user = User::factory()->create();
    $club = Club::factory()->create();

    Member::factory()->for($club)->for($user)->create();

    return [$user, $club];
}

/**
 * @param  array<int, array<string, mixed>>  $members
 */
function memberImportJson(array $members): string
{
    return json_encode(['members' => $members], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
}

test('a member can import new members with optional fields', function () {
    [$user, $club] = createMemberImportContext();

    $this->actingAs($user)
        ->post(route('clubs.settings.members.import', $club), [
            'json' => memberImportJson([
                ['name' => 'Taylor Smith', 'email' => 'Taylor@example.com', 'phone' => '+47 999 99 999'],
                ['name' => 'Alex Jones'],
            ]),
        ])
        ->assertRedirectToRoute('clubs.settings.members', $club)
        ->assertSessionHas('status', 'Import complete: 2 created, 0 updated.');

    $members = $club->members()->whereIn('name', ['Alex Jones', 'Taylor Smith'])->orderBy('name')->get();

    expect($members)->toHaveCount(2)
        ->and($members->first()->email)->toBeNull()
        ->and($members->last()->email)->toBe('taylor@example.com')
        ->and($members->last()->phone)->toBe('+47 999 99 999');
});

test('an import updates a matching member without clearing omitted or empty contact fields', function () {
    [$user, $club] = createMemberImportContext();
    $member = Member::factory()->for($club)->create([
        'name' => 'Old name',
        'email' => 'member@example.com',
        'phone' => '+47 111 11 111',
    ]);

    $this->actingAs($user)
        ->post(route('clubs.settings.members.import', $club), [
            'json' => memberImportJson([
                ['name' => 'New name', 'email' => ' MEMBER@EXAMPLE.COM ', 'phone' => ''],
            ]),
        ])
        ->assertRedirectToRoute('clubs.settings.members', $club)
        ->assertSessionHas('status', 'Import complete: 0 created, 1 updated.');

    expect($member->refresh()->name)->toBe('New name')
        ->and($member->phone)->toBe('+47 111 11 111');
});

test('an import rejects invalid input without changing members', function (string $json, array $errors) {
    [$user, $club] = createMemberImportContext();
    $initialCount = $club->members()->count();

    $this->actingAs($user)
        ->post(route('clubs.settings.members.import', $club), ['json' => $json])
        ->assertSessionHasErrors($errors);

    expect($club->members()->count())->toBe($initialCount);
})->with([
    'invalid JSON' => ['{not valid', ['json']],
    'missing name' => [memberImportJson([['email' => 'member@example.com']]), ['members.0.name']],
    'invalid email' => [memberImportJson([['name' => 'Taylor Smith', 'email' => 'invalid']]), ['members.0.email']],
    'duplicate normalized email' => [memberImportJson([
        ['name' => 'Taylor Smith', 'email' => 'member@example.com'],
        ['name' => 'Alex Jones', 'email' => 'MEMBER@example.com'],
    ]), ['members.1.email']],
    'more than 100 members' => [memberImportJson(array_map(
        fn (int $index): array => ['name' => "Member {$index}"],
        range(1, 101),
    )), ['members']],
]);

test('users without club access cannot view or import members', function () {
    $user = User::factory()->create();
    $club = Club::factory()->create();

    $this->actingAs($user)
        ->get(route('clubs.settings.members', $club))
        ->assertNotFound();

    $this->actingAs($user)
        ->post(route('clubs.settings.members.import', $club), ['json' => memberImportJson([])])
        ->assertNotFound();
});

test('the member import page shows instructions and navigation', function () {
    [$user, $club] = createMemberImportContext();

    $this->actingAs($user)
        ->get(route('clubs.settings.members', $club))
        ->assertSuccessful()
        ->assertSee(__('members.import.title'))
        ->assertSee(__('members.import.fields'))
        ->assertSee(__('members.import.agent_prompt'))
        ->assertSee(__('app.navigation.member_import'))
        ->assertSee(route('clubs.settings.members', $club), false);
});
