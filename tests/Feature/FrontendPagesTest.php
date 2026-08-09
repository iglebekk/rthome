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
        [route('clubs.settings.events', $club), __('clubs.settings.events.title')],
        [route('profile.show'), __('profile.title')],
        [route('clubs.create'), __('clubs.create.title')],
    ];

    foreach ($pages as [$url, $text]) {
        $this->actingAs($user)->get($url)->assertSuccessful()->assertSee($text);
    }
});

test('club settings events page is scoped to the current club', function () {
    $user = User::factory()->create();
    $club = Club::factory()->create();
    $otherClub = Club::factory()->create();
    Member::factory()->for($club)->for($user)->create();

    $this->actingAs($user)
        ->get(route('clubs.settings.events', $club))
        ->assertSuccessful()
        ->assertSee(__('clubs.settings.events.title'))
        ->assertSee(__('clubs.settings.events.description'))
        ->assertSee(route('clubs.settings.events', $club), false);

    $this->actingAs($user)
        ->get(route('clubs.settings.events', $otherClub))
        ->assertNotFound();
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

test('event details include a Takt calendar link', function () {
    $user = User::factory()->create();
    $club = Club::factory()->create();
    Member::factory()->for($club)->for($user)->create();
    $event = Event::factory()->for($club)->create([
        'name' => 'Annual meetup',
        'starts_at' => '2030-01-15 16:00:00',
        'ends_at' => '2030-01-15 18:00:00',
    ]);

    $this->actingAs($user)->get(route('clubs.events.show', [$club, $event]))
        ->assertSuccessful()
        ->assertSee(__('events.actions.add_to_calendar'))
        ->assertSee('href="https://takt.on-forge.com/create?title=Annual%20meetup', false)
        ->assertSee('target="_blank"', false)
        ->assertSee('rel="noopener noreferrer"', false);
});

test('profile sidebar links back to the club workspace', function () {
    $user = User::factory()->create();
    $club = Club::factory()->create();
    Member::factory()->for($club)->for($user)->create();

    $this->actingAs($user)->get(route('profile.show'))
        ->assertSuccessful()
        ->assertSee(__('app.navigation.profile'))
        ->assertSee(__('app.navigation.back_to_club'))
        ->assertSee(route('home'), false);
});

test('club settings submenu renders with the correct state and destination', function () {
    $user = User::factory()->create();
    $club = Club::factory()->create();
    Member::factory()->for($club)->for($user)->create();

    foreach ([
        'closed' => [route('clubs.dashboard', $club), 'false'],
        'open on club details' => [route('clubs.edit', $club), 'true'],
    ] as [$url, $settingsOpen]) {
        $this->actingAs($user)->get($url)
            ->assertSuccessful()
            ->assertSee(__('app.navigation.settings'))
            ->assertSee(__('app.navigation.club_details'))
            ->assertSee('x-data="{ settingsOpen: '.$settingsOpen.' }"', false)
            ->assertSee('href="'.route('clubs.edit', $club).'"', false);
    }
});

test('sidebar hides club navigation while keeping profile navigation', function () {
    $user = User::factory()->create();
    $club = Club::factory()->create();
    Member::factory()->for($club)->for($user)->create();

    foreach ([route('clubs.index'), route('clubs.dashboard', $club), route('profile.show')] as $url) {
        $this->actingAs($user)
            ->get($url)
            ->assertSuccessful()
            ->assertSee(__('app.navigation.profile'))
            ->assertDontSee('View all Clubs')
            ->assertDontSee(__('app.navigation.club'));
    }
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
