<?php

use App\Models\Club;
use App\Models\Event;
use App\Models\Member;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('guest authentication pages render', function (string $routeName, string $text) {
    $this->get(route($routeName))->assertSuccessful()->assertSee($text);
})->with([
    'identify' => ['login', fn () => __('auth.identify.title')],
    'register' => ['register', fn () => __('auth.register.title')],
    'forgot password' => ['password.request', fn () => __('auth.forgot.title')],
    'activation sent' => ['activation.sent', fn () => __('auth.activation.sent_title')],
]);

test('every club administration page renders on desktop-compatible markup', function () {
    $user = User::factory()->create();
    $club = Club::factory()->create();
    $member = Member::factory()->for($club)->for($user)->create();
    $position = Position::factory()->for($club)->for($member)->create();
    $event = Event::factory()->for($club)->create();

    $pages = [
        [route('clubs.dashboard', $club), __('dashboard.description')],
        [route('clubs.members.index', $club), __('members.title')],
        [route('clubs.members.create', $club), __('members.create_title')],
        [route('clubs.members.edit', [$club, $member]), __('members.edit_title')],
        [route('clubs.positions.index', $club), __('positions.title')],
        [route('clubs.positions.create', $club), __('positions.create_title')],
        [route('clubs.positions.edit', [$club, $position]), __('positions.edit_title')],
        [route('clubs.events.index', $club), __('events.title')],
        [route('clubs.events.create', $club), __('events.create_title')],
        [route('clubs.events.show', [$club, $event]), __('events.view_title')],
        [route('clubs.events.edit', [$club, $event]), __('events.edit_title')],
        [route('clubs.edit', $club), __('clubs.settings.title')],
        [route('profile.show'), __('profile.title')],
        [route('clubs.create'), __('clubs.create.title')],
    ];

    foreach ($pages as [$url, $text]) {
        $this->actingAs($user)->get($url)->assertSuccessful()->assertSee($text);
    }
});

test('empty states and destructive warnings render their full meaning', function () {
    $user = User::factory()->create();
    $club = Club::factory()->create();
    Member::factory()->for($club)->for($user)->create();

    $this->actingAs($user)->get(route('clubs.events.index', $club))
        ->assertSuccessful()
        ->assertSee(__('events.empty_upcoming'));

    $this->actingAs($user)->get(route('clubs.edit', $club))
        ->assertSuccessful()
        ->assertSee(__('clubs.settings.danger_description'));

    $this->actingAs($user)->get(route('clubs.members.index', $club))
        ->assertSuccessful()
        ->assertSee(__('members.delete_description'));
});

test('events index cards link to their event details', function () {
    $user = User::factory()->create();
    $club = Club::factory()->create();
    Member::factory()->for($club)->for($user)->create();
    $event = Event::factory()->for($club)->create();

    $this->actingAs($user)->get(route('clubs.events.index', $club))
        ->assertSuccessful()
        ->assertSee('<a href="'.route('clubs.events.show', [$club, $event]).'"', false)
        ->assertDontSee(route('clubs.events.edit', [$club, $event]), false);
});

test('profile sidebar links back to the club workspace', function () {
    $user = User::factory()->create();
    $club = Club::factory()->create();
    Member::factory()->for($club)->for($user)->create();

    $this->actingAs($user)->get(route('profile.show'))
        ->assertSuccessful()
        ->assertSee(__('app.navigation.back_to_club'))
        ->assertSee(route('home'), false);
});

test('sidebar links to all clubs below profile and does not render the club switcher', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('clubs.index'))
        ->assertSuccessful()
        ->assertSeeInOrder([
            __('app.navigation.profile'),
            __('app.navigation.clubs'),
        ])
        ->assertDontSee(__('app.navigation.club'));
});

test('club pages return not found across the club boundary', function () {
    $user = User::factory()->create();
    $otherClub = Club::factory()->create();

    $this->actingAs($user)->get(route('clubs.dashboard', $otherClub))->assertNotFound();
    $this->actingAs($user)->get(route('clubs.members.index', $otherClub))->assertNotFound();
    $this->actingAs($user)->get(route('clubs.events.index', $otherClub))->assertNotFound();
    $this->actingAs($user)->get(route('clubs.events.show', [$otherClub, Event::factory()->for($otherClub)->create()]))->assertNotFound();
    $this->actingAs($user)->get(route('clubs.positions.index', $otherClub))->assertNotFound();
});
