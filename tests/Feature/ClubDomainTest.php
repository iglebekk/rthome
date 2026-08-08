<?php

use App\Models\Club;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;

uses(LazilyRefreshDatabase::class);

test('creating a club also creates its first member', function () {
    $user = User::factory()->create([
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
    ]);

    $this->actingAs($user)
        ->post(route('clubs.store'), ['name' => 'Computing Club'])
        ->assertRedirect();

    $club = $user->clubs()->with('members')->sole();
    $member = $club->members->sole();

    expect($club->name)->toBe('Computing Club')
        ->and($member->user->is($user))->toBeTrue()
        ->and($member->name)->toBe($user->name)
        ->and($member->email)->toBe($user->email)
        ->and($club->users()->sole()->is($user))->toBeTrue()
        ->and($user->members()->sole()->is($member))->toBeTrue()
        ->and($user->clubs()->sole()->is($club))->toBeTrue();
});

test('club creation validates its name', function (?string $name) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('clubs.store'), ['name' => $name])
        ->assertSessionHasErrors('name');

    expect($user->clubs()->count())->toBe(0);
})->with([
    'missing name' => null,
    'name over 255 characters' => fn () => Str::repeat('a', 256),
]);

test('a guest may not create a club', function () {
    $this->post(route('clubs.store'), ['name' => 'Computing Club'])
        ->assertForbidden();
});

test('a user may belong to several clubs but only once per club', function () {
    $user = User::factory()->create();
    $firstClub = Club::factory()->create();
    $secondClub = Club::factory()->create();

    Member::factory()->for($firstClub)->for($user)->create();
    Member::factory()->for($secondClub)->for($user)->create();

    expect($user->clubs()->count())->toBe(2)
        ->and(fn () => Member::factory()->for($firstClub)->for($user)->create())
        ->toThrow(QueryException::class);
});

test('a member may exist without a user', function () {
    $member = Member::factory()->create();

    expect($member->user_id)->toBeNull()
        ->and($member->user)->toBeNull()
        ->and($member->club)->toBeInstanceOf(Club::class);
});

test('member email is unique within a club but reusable in another club', function () {
    $firstClub = Club::factory()->create();
    $secondClub = Club::factory()->create();
    $email = 'member@example.com';

    Member::factory()->for($firstClub)->create(['email' => $email]);
    Member::factory()->for($secondClub)->create(['email' => $email]);

    expect(fn () => Member::factory()->for($firstClub)->create(['email' => $email]))
        ->toThrow(QueryException::class);
});

test('deleting a user detaches the member profile', function () {
    $user = User::factory()->create();
    $member = Member::factory()->for($user)->create();

    $user->delete();

    expect($member->refresh()->user_id)->toBeNull();
    $this->assertModelExists($member);
});

test('deleting a club deletes all of its members', function () {
    $club = Club::factory()->create();
    $members = Member::factory()->count(2)->for($club)->create();

    $club->delete();

    $this->assertModelMissing($members[0]);
    $this->assertModelMissing($members[1]);
});

test('deleting one of several members keeps the club', function () {
    $club = Club::factory()->create();
    $user = User::factory()->create();
    Member::factory()->for($club)->for($user)->create();
    $member = Member::factory()->for($club)->create();

    $this->actingAs($user)
        ->delete(route('clubs.members.destroy', [$club, $member]))
        ->assertRedirect();

    $this->assertModelMissing($member);
    $this->assertModelExists($club);
    expect($club->members()->count())->toBe(1);
});

test('deleting the final member deletes the club', function () {
    $club = Club::factory()->create();
    $user = User::factory()->create();
    $member = Member::factory()->for($club)->for($user)->create();

    $this->actingAs($user)
        ->delete(route('clubs.members.destroy', [$club, $member]))
        ->assertRedirect();

    $this->assertModelMissing($member);
    $this->assertModelMissing($club);
    $this->assertModelExists($user);
});

test('club members may manage their club and its members', function () {
    $club = Club::factory()->create();
    $user = User::factory()->create();
    Member::factory()->for($club)->for($user)->create();
    $otherMember = Member::factory()->for($club)->create();

    expect($user->can('update', $club))->toBeTrue()
        ->and($user->can('delete', $club))->toBeTrue()
        ->and($user->can('create', [Member::class, $club]))->toBeTrue()
        ->and($user->can('update', $otherMember))->toBeTrue()
        ->and($user->can('delete', $otherMember))->toBeTrue();
});

test('outsiders and members of another club may not manage club data', function () {
    $firstClub = Club::factory()->create();
    $secondClub = Club::factory()->create();
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();
    $outsider = User::factory()->create();
    Member::factory()->for($firstClub)->for($firstUser)->create();
    $secondMember = Member::factory()->for($secondClub)->for($secondUser)->create();

    expect($firstUser->can('update', $secondClub))->toBeFalse()
        ->and($firstUser->can('create', [Member::class, $secondClub]))->toBeFalse()
        ->and($firstUser->can('update', $secondMember))->toBeFalse()
        ->and($firstUser->can('delete', $secondMember))->toBeFalse()
        ->and($outsider->can('delete', $firstClub))->toBeFalse();

    $this->actingAs($firstUser)
        ->delete(route('clubs.members.destroy', [$secondClub, $secondMember]))
        ->assertForbidden();

    $this->assertModelExists($secondMember);
});

test('nested member binding does not resolve a member from another club', function () {
    $firstClub = Club::factory()->create();
    $secondClub = Club::factory()->create();
    $user = User::factory()->create();
    Member::factory()->for($firstClub)->for($user)->create();
    $secondMember = Member::factory()->for($secondClub)->create();

    $this->actingAs($user)
        ->delete(route('clubs.members.destroy', [$firstClub, $secondMember]))
        ->assertNotFound();

    $this->assertModelExists($secondMember);
});
