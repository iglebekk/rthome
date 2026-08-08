<?php

use App\Models\Club;
use App\Models\Link;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

/**
 * @return array{0: User, 1: Club}
 */
function createLinkClubMember(): array
{
    $user = User::factory()->create();
    $club = Club::factory()->create();

    Member::factory()->for($club)->for($user)->create();

    return [$user, $club];
}

test('links belong to clubs and allow duplicate urls', function () {
    $club = Club::factory()->create();
    $url = 'https://example.com/resource';

    $firstLink = Link::factory()->for($club)->create(['url' => $url]);
    $secondLink = Link::factory()->for($club)->create(['url' => $url]);

    expect($firstLink->club->is($club))->toBeTrue()
        ->and($club->links()->count())->toBe(2)
        ->and($secondLink->club->is($club))->toBeTrue();
});

test('deleting a club deletes its links', function () {
    $club = Club::factory()->create();
    $link = Link::factory()->for($club)->create();

    $club->delete();

    $this->assertModelMissing($link);
});

test('a club member can create, update, pin, and delete a link', function () {
    [$user, $club] = createLinkClubMember();

    $createResponse = $this->actingAs($user)->post(route('clubs.links.store', $club), [
        'name' => 'Club handbook',
        'url' => 'https://example.com/handbook',
        'is_pinned' => '1',
    ]);

    $createResponse->assertRedirect(route('clubs.links.index', $club));
    $link = Link::query()->where('name', 'Club handbook')->sole();
    expect($link->is_pinned)->toBeTrue();

    $updateResponse = $this->actingAs($user)->put(route('clubs.links.update', [$club, $link]), [
        'name' => 'Updated handbook',
        'url' => 'https://example.com/updated-handbook',
        'is_pinned' => '0',
    ]);

    $updateResponse->assertRedirect(route('clubs.links.index', $club));
    expect($link->refresh()->name)->toBe('Updated handbook')
        ->and($link->is_pinned)->toBeFalse();

    $deleteResponse = $this->actingAs($user)->delete(route('clubs.links.destroy', [$club, $link]));

    $deleteResponse->assertRedirect(route('clubs.links.index', $club));
    $this->assertModelMissing($link);
});

test('link validation requires a valid name and url', function () {
    [$user, $club] = createLinkClubMember();

    $response = $this->actingAs($user)->from(route('clubs.links.create', $club))
        ->post(route('clubs.links.store', $club), [
            'name' => '',
            'url' => 'not-a-url',
        ]);

    $response->assertRedirect(route('clubs.links.create', $club))
        ->assertSessionHasErrors(['name', 'url']);
});

test('a non-member cannot read or change another clubs links', function () {
    [$member, $club] = createLinkClubMember();
    $outsider = User::factory()->create();
    $link = Link::factory()->for($club)->create();

    $this->actingAs($outsider)->get(route('clubs.links.index', $club))->assertNotFound();
    $this->actingAs($outsider)->post(route('clubs.links.store', $club), [
        'name' => 'Blocked',
        'url' => 'https://example.com/blocked',
    ])->assertNotFound();
    $this->actingAs($outsider)->put(route('clubs.links.update', [$club, $link]), [
        'name' => 'Blocked',
        'url' => 'https://example.com/blocked',
        'is_pinned' => '0',
    ])->assertNotFound();
    $this->actingAs($outsider)->delete(route('clubs.links.destroy', [$club, $link]))->assertNotFound();

    expect($link->refresh()->name)->not->toBe('Blocked')
        ->and($member->can('update', $link))->toBeTrue();
});

test('links sort pinned first and newest first within each group', function () {
    [$user, $club] = createLinkClubMember();
    $this->travelTo(now()->startOfSecond());

    $oldPinned = Link::factory()->for($club)->create(['name' => 'Old pinned', 'is_pinned' => true]);
    $newUnpinned = Link::factory()->for($club)->create(['name' => 'New unpinned']);
    $newPinned = Link::factory()->for($club)->create(['name' => 'New pinned', 'is_pinned' => true]);
    $oldUnpinned = Link::factory()->for($club)->create(['name' => 'Old unpinned']);
    $oldPinned->created_at = now()->subMinutes(4);
    $oldPinned->save();
    $oldUnpinned->created_at = now()->subMinutes(3);
    $oldUnpinned->save();

    $response = $this->actingAs($user)->get(route('clubs.links.index', $club));

    $response->assertSuccessful()
        ->assertSeeInOrder(['New pinned', 'Old pinned', 'New unpinned', 'Old unpinned']);
});

test('dashboard shows at most ten links even when all are pinned', function () {
    [$user, $club] = createLinkClubMember();
    Link::factory()->count(12)->for($club)->create(['is_pinned' => true]);

    $response = $this->actingAs($user)->get(route('clubs.dashboard', $club));

    $response->assertSuccessful();
    expect($response->viewData('links'))->toHaveCount(10);
});

test('links are isolated between clubs', function () {
    [$user, $club] = createLinkClubMember();
    $otherClub = Club::factory()->create();
    Link::factory()->for($club)->create(['name' => 'First club link']);
    Link::factory()->for($otherClub)->create(['name' => 'Second club link']);

    $response = $this->actingAs($user)->get(route('clubs.links.index', $club));

    $response->assertSee('First club link')->assertDontSee('Second club link');
});
