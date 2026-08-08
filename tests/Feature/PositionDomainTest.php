<?php

use App\Models\Club;
use App\Models\Member;
use App\Models\Position;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;

uses(LazilyRefreshDatabase::class);

/**
 * @return array{0: User, 1: Club, 2: Member}
 */
function createPositionClubMember(): array
{
    $user = User::factory()->create();
    $club = Club::factory()->create();
    $member = Member::factory()->for($club)->for($user)->create();

    return [$user, $club, $member];
}

test('positions have a valid factory, relationships, and date casts', function () {
    $position = Position::factory()->create([
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
    ]);

    expect($position->club)->toBeInstanceOf(Club::class)
        ->and($position->member)->toBeInstanceOf(Member::class)
        ->and($position->member->club->is($position->club))->toBeTrue()
        ->and($position->club->positions()->sole()->is($position))->toBeTrue()
        ->and($position->member->positions()->sole()->is($position))->toBeTrue()
        ->and($position->start_date)->toBeInstanceOf(CarbonInterface::class)
        ->and($position->end_date)->toBeInstanceOf(CarbonInterface::class)
        ->and($position->start_date->toDateString())->toBe('2026-01-01')
        ->and($position->end_date->toDateString())->toBe('2026-12-31');
});

test('a member may hold several positions', function () {
    $club = Club::factory()->create();
    $member = Member::factory()->for($club)->create();

    Position::factory()->count(2)->for($club)->for($member)->create();

    expect($member->positions()->count())->toBe(2);
});

test('position names are unique per club and reusable in another club', function () {
    $firstClub = Club::factory()->create();
    $secondClub = Club::factory()->create();
    $firstMember = Member::factory()->for($firstClub)->create();
    $secondMember = Member::factory()->for($secondClub)->create();

    Position::factory()->for($firstClub)->for($firstMember)->create(['name' => 'President']);
    Position::factory()->for($secondClub)->for($secondMember)->create(['name' => 'President']);

    expect(fn () => Position::factory()
        ->for($firstClub)
        ->for($firstMember)
        ->create(['name' => 'President']))
        ->toThrow(QueryException::class);
});

test('deleting a club deletes its positions', function () {
    $club = Club::factory()->create();
    $member = Member::factory()->for($club)->create();
    $position = Position::factory()->for($club)->for($member)->create();

    $club->delete();

    $this->assertModelMissing($position);
});

test('deleting a member keeps its positions without a member', function () {
    $club = Club::factory()->create();
    $member = Member::factory()->for($club)->create();
    $position = Position::factory()->for($club)->for($member)->create();

    $member->delete();

    expect($position->refresh()->member_id)->toBeNull()
        ->and($position->member)->toBeNull();
    $this->assertModelExists($position);
});

test('a club member can create, show, update, and delete a position', function () {
    [$user, $club, $member] = createPositionClubMember();
    $newMember = Member::factory()->for($club)->create();

    Position::factory()->for($club)->for($member)->create([
        'name' => 'Existing position',
        'sort_order' => 7,
    ]);

    $createResponse = $this->actingAs($user)->postJson(route('clubs.positions.store', $club), [
        'member_id' => $member->id,
        'name' => 'President',
        'start_date' => '2026-01-01',
    ]);

    $createResponse->assertCreated()
        ->assertJsonPath('data.club_id', $club->id)
        ->assertJsonPath('data.member_id', $member->id)
        ->assertJsonPath('data.member.id', $member->id)
        ->assertJsonPath('data.member.name', $member->name)
        ->assertJsonPath('data.name', 'President')
        ->assertJsonPath('data.sort_order', 8)
        ->assertJsonPath('data.start_date', '2026-01-01')
        ->assertJsonPath('data.end_date', null)
        ->assertJsonStructure([
            'data' => [
                'id',
                'club_id',
                'member_id',
                'name',
                'sort_order',
                'start_date',
                'end_date',
                'member' => ['id', 'name', 'email', 'phone'],
                'created_at',
                'updated_at',
            ],
        ]);

    $position = $club->positions()->where('name', 'President')->sole();

    $this->actingAs($user)
        ->getJson(route('clubs.positions.show', [$club, $position]))
        ->assertSuccessful()
        ->assertJsonPath('data.id', $position->id)
        ->assertJsonPath('data.member.id', $member->id);

    $this->actingAs($user)
        ->patchJson(route('clubs.positions.update', [$club, $position]), [
            'member_id' => $newMember->id,
            'name' => 'Chair',
            'sort_order' => 0,
            'start_date' => '2026-02-01',
            'end_date' => '2026-12-31',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.member.id', $newMember->id)
        ->assertJsonPath('data.name', 'Chair')
        ->assertJsonPath('data.sort_order', 0)
        ->assertJsonPath('data.end_date', '2026-12-31');

    expect($position->refresh()->member->is($newMember))->toBeTrue();

    $this->actingAs($user)
        ->deleteJson(route('clubs.positions.destroy', [$club, $position]))
        ->assertNoContent();

    $this->assertModelMissing($position);
});

test('the first automatically ordered position starts at zero', function () {
    [$user, $club, $member] = createPositionClubMember();

    $this->actingAs($user)
        ->postJson(route('clubs.positions.store', $club), [
            'member_id' => $member->id,
            'name' => 'President',
        ])
        ->assertCreated()
        ->assertJsonPath('data.sort_order', 0);
});

test('the index returns all positions ordered manually and then by id with member data', function () {
    [$user, $club, $member] = createPositionClubMember();
    $firstAtSameOrder = Position::factory()->for($club)->for($member)->create([
        'name' => 'Secretary',
        'sort_order' => 2,
    ]);
    $last = Position::factory()->for($club)->for($member)->create([
        'name' => 'Board Member',
        'sort_order' => 8,
    ]);
    $secondAtSameOrder = Position::factory()->for($club)->for($member)->create([
        'name' => 'Treasurer',
        'sort_order' => 2,
    ]);

    $previousLazyLoadingState = Model::preventsLazyLoading();
    Model::preventLazyLoading();

    try {
        $response = $this->actingAs($user)
            ->getJson(route('clubs.positions.index', $club));
    } finally {
        Model::preventLazyLoading($previousLazyLoadingState);
    }

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('data.0.id', $firstAtSameOrder->id)
        ->assertJsonPath('data.0.member.id', $member->id)
        ->assertJsonPath('data.1.id', $secondAtSameOrder->id)
        ->assertJsonPath('data.2.id', $last->id);
});

test('an orphaned position can be shown and reassigned', function () {
    [$user, $club] = createPositionClubMember();
    $formerMember = Member::factory()->for($club)->create();
    $newMember = Member::factory()->for($club)->create();
    $position = Position::factory()->for($club)->for($formerMember)->create();

    $formerMember->delete();

    $this->actingAs($user)
        ->getJson(route('clubs.positions.show', [$club, $position]))
        ->assertSuccessful()
        ->assertJsonPath('data.member_id', null)
        ->assertJsonPath('data.member', null);

    $this->actingAs($user)
        ->patchJson(route('clubs.positions.update', [$club, $position]), [
            'member_id' => $newMember->id,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.member.id', $newMember->id);
});

test('position creation validates names and sort order', function (array $overrides, string $error) {
    [$user, $club, $member] = createPositionClubMember();

    $this->actingAs($user)
        ->postJson(route('clubs.positions.store', $club), [
            'member_id' => $member->id,
            'name' => 'President',
            ...$overrides,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors($error);
})->with([
    'missing name' => [['name' => null], 'name'],
    'long name' => [['name' => Str::repeat('a', 256)], 'name'],
    'negative sort order' => [['sort_order' => -1], 'sort_order'],
    'decimal sort order' => [['sort_order' => 1.5], 'sort_order'],
]);

test('position creation enforces name uniqueness within the club', function () {
    [$user, $club, $member] = createPositionClubMember();
    Position::factory()->for($club)->for($member)->create(['name' => 'President']);

    $this->actingAs($user)
        ->postJson(route('clubs.positions.store', $club), [
            'member_id' => $member->id,
            'name' => 'President',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

test('position creation requires a member from the same club', function (?int $memberId) {
    [$user, $club] = createPositionClubMember();

    $this->actingAs($user)
        ->postJson(route('clubs.positions.store', $club), [
            'member_id' => $memberId,
            'name' => 'President',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('member_id');
})->with([
    'missing member' => null,
    'member from another club' => fn () => Member::factory()->create()->id,
]);

test('position creation validates date periods while allowing a start date alone', function () {
    [$user, $club, $member] = createPositionClubMember();

    $this->actingAs($user)
        ->postJson(route('clubs.positions.store', $club), [
            'member_id' => $member->id,
            'name' => 'Valid start only',
            'start_date' => '2026-01-01',
        ])
        ->assertCreated();

    $this->actingAs($user)
        ->postJson(route('clubs.positions.store', $club), [
            'member_id' => $member->id,
            'name' => 'Missing start',
            'end_date' => '2026-12-31',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('start_date');

    $this->actingAs($user)
        ->postJson(route('clubs.positions.store', $club), [
            'member_id' => $member->id,
            'name' => 'Invalid order',
            'start_date' => '2026-12-31',
            'end_date' => '2026-01-01',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('end_date');

    $this->actingAs($user)
        ->postJson(route('clubs.positions.store', $club), [
            'member_id' => $member->id,
            'name' => 'Invalid format',
            'start_date' => '01.01.2026',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('start_date');
});

test('position updates validate explicit member and name changes', function () {
    [$user, $club, $member] = createPositionClubMember();
    $position = Position::factory()->for($club)->for($member)->create(['name' => 'President']);
    Position::factory()->for($club)->for($member)->create(['name' => 'Treasurer']);
    $otherClubMember = Member::factory()->create();

    $this->actingAs($user)
        ->patchJson(route('clubs.positions.update', [$club, $position]), ['member_id' => null])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('member_id');

    $this->actingAs($user)
        ->patchJson(route('clubs.positions.update', [$club, $position]), [
            'member_id' => $otherClubMember->id,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('member_id');

    $this->actingAs($user)
        ->patchJson(route('clubs.positions.update', [$club, $position]), ['name' => 'Treasurer'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

test('position updates validate the effective date period', function () {
    [$user, $club, $member] = createPositionClubMember();
    $position = Position::factory()->for($club)->for($member)->create([
        'start_date' => '2026-02-01',
        'end_date' => '2026-10-01',
    ]);

    $this->actingAs($user)
        ->patchJson(route('clubs.positions.update', [$club, $position]), [
            'end_date' => '2026-01-31',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('end_date');

    $this->actingAs($user)
        ->patchJson(route('clubs.positions.update', [$club, $position]), [
            'start_date' => '2026-10-02',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('end_date');

    $position->update(['start_date' => null, 'end_date' => null]);

    $this->actingAs($user)
        ->patchJson(route('clubs.positions.update', [$club, $position]), [
            'end_date' => '2026-12-31',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('start_date');

    $this->actingAs($user)
        ->patchJson(route('clubs.positions.update', [$club, $position]), [
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-01',
        ])
        ->assertSuccessful();
});

test('club members may manage positions while outsiders may not', function () {
    [$memberUser, $club, $member] = createPositionClubMember();
    $position = Position::factory()->for($club)->for($member)->create();
    $otherClubUser = User::factory()->create();
    Member::factory()->for($otherClubUser)->create();
    $outsider = User::factory()->create();

    expect($memberUser->can('create', [Position::class, $club]))->toBeTrue()
        ->and($memberUser->can('update', $position))->toBeTrue()
        ->and($memberUser->can('delete', $position))->toBeTrue()
        ->and($otherClubUser->can('create', [Position::class, $club]))->toBeFalse()
        ->and($otherClubUser->can('update', $position))->toBeFalse()
        ->and($otherClubUser->can('delete', $position))->toBeFalse()
        ->and($outsider->can('delete', $position))->toBeFalse();

    $this->actingAs($otherClubUser)
        ->getJson(route('clubs.positions.index', $club))
        ->assertNotFound();

    $this->actingAs($outsider)
        ->getJson(route('clubs.positions.show', [$club, $position]))
        ->assertNotFound();

    $this->actingAs($otherClubUser)
        ->postJson(route('clubs.positions.store', $club), [
            'member_id' => $member->id,
            'name' => 'Forbidden',
        ])
        ->assertNotFound();

    $this->actingAs($otherClubUser)
        ->patchJson(route('clubs.positions.update', [$club, $position]), ['name' => 'Forbidden'])
        ->assertNotFound();

    $this->actingAs($otherClubUser)
        ->deleteJson(route('clubs.positions.destroy', [$club, $position]))
        ->assertNotFound();
});

test('guests cannot access position endpoints', function () {
    $club = Club::factory()->create();
    $member = Member::factory()->for($club)->create();
    $position = Position::factory()->for($club)->for($member)->create();

    $this->getJson(route('clubs.positions.index', $club))->assertUnauthorized();
    $this->getJson(route('clubs.positions.show', [$club, $position]))->assertUnauthorized();
    $this->postJson(route('clubs.positions.store', $club), [])->assertUnauthorized();
    $this->patchJson(route('clubs.positions.update', [$club, $position]), [])->assertUnauthorized();
    $this->deleteJson(route('clubs.positions.destroy', [$club, $position]))->assertUnauthorized();
});

test('a position cannot be accessed through another club', function () {
    [$user, $firstClub] = createPositionClubMember();
    $secondClub = Club::factory()->create();
    $secondMember = Member::factory()->for($secondClub)->create();
    $position = Position::factory()->for($secondClub)->for($secondMember)->create();

    $this->actingAs($user)
        ->getJson(route('clubs.positions.show', [$firstClub, $position]))
        ->assertNotFound();

    $this->actingAs($user)
        ->patchJson(route('clubs.positions.update', [$firstClub, $position]), ['name' => 'Cross-club'])
        ->assertNotFound();

    $this->actingAs($user)
        ->deleteJson(route('clubs.positions.destroy', [$firstClub, $position]))
        ->assertNotFound();

    $this->assertModelExists($position);
});
