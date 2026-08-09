<?php

use App\Models\Club;
use App\Models\Event;
use App\Models\Link;
use App\Models\Member;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('a user without a club is directed to club creation from home', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertRedirectToRoute('clubs.create');

    $this->actingAs($user)
        ->get(route('clubs.create'))
        ->assertSuccessful()
        ->assertSee(__('clubs.create.title'));
});

test('dashboard shows filled positions with their members and excludes open positions', function () {
    $user = User::factory()->create();
    $club = Club::factory()->create();
    $member = Member::factory()->for($club)->for($user)->create(['name' => 'Alex Member']);

    foreach (range(1, 6) as $sortOrder) {
        Position::factory()->for($club)->for($member)->create([
            'name' => "Filled position {$sortOrder}",
            'sort_order' => $sortOrder,
        ]);
    }

    Position::factory()->for($club)->create([
        'name' => 'Open position',
        'member_id' => null,
        'sort_order' => 0,
    ]);

    $response = $this->actingAs($user)->get(route('clubs.dashboard', $club));
    $filledPositions = $response->viewData('filledPositions');

    $response->assertSuccessful()
        ->assertSee('Filled position 1')
        ->assertSee('Filled position 5')
        ->assertSee('Alex Member')
        ->assertDontSee('Filled position 6')
        ->assertDontSee('Open position')
        ->assertSee(__('positions.title'))
        ->assertDontSee('Filled positions');

    expect($filledPositions)->toHaveCount(5)
        ->and($filledPositions->pluck('sort_order')->all())->toBe([1, 2, 3, 4, 5])
        ->and($filledPositions->every(fn (Position $position): bool => $position->relationLoaded('member')))->toBeTrue();
});

test('dashboard shows the filled positions empty state when no positions have members', function () {
    $user = User::factory()->create();
    $club = Club::factory()->create();
    Member::factory()->for($club)->for($user)->create();
    Position::factory()->for($club)->create(['member_id' => null]);

    $response = $this->actingAs($user)->get(route('clubs.dashboard', $club));

    $response->assertSuccessful()
        ->assertSee(__('dashboard.empty_filled_positions'))
        ->assertSee('col-span-full', false);
});

test('dashboard shows three upcoming events in compact form with club links', function () {
    $user = User::factory()->create();
    $club = Club::factory()->create();
    Member::factory()->for($club)->for($user)->create();

    $events = [];
    foreach (range(1, 3) as $number) {
        $events[] = Event::factory()->for($club)->create([
            'name' => "Dashboard event {$number}",
            'location' => "Club room {$number}",
            'short_description' => "Dashboard event description {$number}",
            'starts_at' => now()->addDays($number),
            'ends_at' => now()->addDays($number)->addHour(),
        ]);
    }

    $link = Link::factory()->for($club)->create(['name' => 'Club handbook']);
    Link::factory()->for(Club::factory())->create(['name' => 'Other club link']);

    $response = $this->actingAs($user)->get(route('clubs.dashboard', $club));

    $response->assertSuccessful()
        ->assertSee('Dashboard event 1')
        ->assertSee('Dashboard event 2')
        ->assertSee('Dashboard event 3')
        ->assertSee('Club room 1')
        ->assertSee('Club handbook')
        ->assertSee('<a href="'.route('clubs.events.show', [$club, $events[0]]).'"', false)
        ->assertSee('<a href="'.$link->url.'" target="_blank"', false)
        ->assertDontSee(route('clubs.events.edit', [$club, $events[0]]), false)
        ->assertDontSee(route('clubs.links.edit', [$club, $link]), false)
        ->assertDontSee('Other club link')
        ->assertSee('View all upcoming events')
        ->assertSee('<a href="'.route('clubs.events.index', $club).'"', false)
        ->assertSee('View all club links')
        ->assertSee('<a href="'.route('clubs.links.index', $club).'"', false)
        ->assertDontSee('<a href="'.route('clubs.events.index', $club).'" data-flux-button', false)
        ->assertDontSee('<a href="'.route('clubs.links.index', $club).'" data-flux-button', false)
        ->assertDontSee('Dashboard event description 1');
});

test('dashboard shows the time until the nearest upcoming event', function () {
    $this->travelTo(now()->startOfSecond());

    $user = User::factory()->create();
    $club = Club::factory()->create();
    Member::factory()->for($club)->for($user)->create();
    Event::factory()->for($club)->create([
        'name' => 'Later event',
        'starts_at' => now()->addDays(4),
        'ends_at' => now()->addDays(4)->addHour(),
    ]);
    $nearestEvent = Event::factory()->for($club)->create([
        'name' => 'Nearest event',
        'starts_at' => now()->addDays(2),
        'ends_at' => now()->addDays(2)->addHour(),
    ]);
    Event::factory()->for(Club::factory())->create(['name' => 'Other club event']);

    $response = $this->actingAs($user)->get(route('clubs.dashboard', $club));

    $response->assertSuccessful()
        ->assertSee('Nearest event')
        ->assertSee($nearestEvent->starts_at->diffForHumans())
        ->assertDontSee('Other club event');

    expect($response->viewData('nextEvent')->is($nearestEvent))->toBeTrue()
        ->and($response->viewData('nextEventCountdown'))->toBe($nearestEvent->starts_at->diffForHumans());
});

test('dashboard shows a create event empty state when no event is upcoming', function () {
    $user = User::factory()->create();
    $club = Club::factory()->create();
    Member::factory()->for($club)->for($user)->create();
    Event::factory()->for($club)->create([
        'starts_at' => now()->subDay(),
        'ends_at' => now()->subDay()->addHour(),
    ]);

    $response = $this->actingAs($user)->get(route('clubs.dashboard', $club));

    $response->assertSuccessful()
        ->assertSee(__('dashboard.empty_next_event'))
        ->assertSee(__('dashboard.empty_next_event_description'))
        ->assertSee(route('clubs.events.create', $club), false);
});
