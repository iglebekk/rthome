<?php

use App\Models\Club;
use App\Models\Event;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

/**
 * @return array{0: User, 1: Club}
 */
function createPublicEventSharingMember(): array
{
    $user = User::factory()->create();
    $club = Club::factory()->create();

    Member::factory()->for($club)->for($user)->create();

    return [$user, $club];
}

test('a member can enable disable and re-enable the same public events link', function () {
    [$user, $club] = createPublicEventSharingMember();

    $this->actingAs($user)
        ->get(route('clubs.events.index', $club))
        ->assertSuccessful()
        ->assertSee('data-test="public-events-enable"', false)
        ->assertDontSee('data-test="public-events-copy"', false);

    $this->actingAs($user)
        ->post(route('clubs.public-events-link.store', $club))
        ->assertRedirectToRoute('clubs.events.index', $club)
        ->assertSessionHas('status', __('events.public_sharing.messages.enabled'));

    $club->refresh();
    $token = $club->public_events_token;
    $publicUrl = route('public-events.index', $token);

    expect($club->public_events_enabled)->toBeTrue()
        ->and($token)->toBeString()->toHaveLength(64);

    $this->actingAs($user)
        ->get(route('clubs.events.index', $club))
        ->assertSuccessful()
        ->assertSee(__('events.public_sharing.actions.copy'))
        ->assertSee('data-test="public-events-copy"', false)
        ->assertSee('data-test="public-events-open"', false)
        ->assertSee('data-test="public-events-disable"', false)
        ->assertSee($publicUrl, false);

    $this->actingAs($user)
        ->delete(route('clubs.public-events-link.destroy', $club))
        ->assertRedirectToRoute('clubs.events.index', $club)
        ->assertSessionHas('status', __('events.public_sharing.messages.disabled'));

    expect($club->refresh()->public_events_enabled)->toBeFalse()
        ->and($club->public_events_token)->toBe($token);

    $this->get($publicUrl)->assertNotFound();

    $this->actingAs($user)->post(route('clubs.public-events-link.store', $club));

    expect($club->refresh()->public_events_token)->toBe($token)
        ->and(route('public-events.index', $club->public_events_token))->toBe($publicUrl);
});

test('each club receives a unique public events token', function () {
    $user = User::factory()->create();
    $clubs = Club::factory()->count(2)->create();

    foreach ($clubs as $club) {
        Member::factory()->for($club)->for($user)->create();
        $this->actingAs($user)->post(route('clubs.public-events-link.store', $club));
    }

    expect($clubs->map(fn (Club $club): ?string => $club->refresh()->public_events_token)->unique())
        ->toHaveCount(2);
});

test('guests are redirected and outsiders receive not found when managing sharing', function () {
    $club = Club::factory()->create();
    $outsider = User::factory()->create();

    $this->post(route('clubs.public-events-link.store', $club))->assertRedirectToRoute('login');
    $this->delete(route('clubs.public-events-link.destroy', $club))->assertRedirectToRoute('login');

    $this->actingAs($outsider)
        ->post(route('clubs.public-events-link.store', $club))
        ->assertNotFound();
    $this->actingAs($outsider)
        ->delete(route('clubs.public-events-link.destroy', $club))
        ->assertNotFound();
});

test('the public index shows only upcoming club events in stable order with pagination', function () {
    $this->travelTo('2030-01-01 12:00:00');

    $club = Club::factory()->withPublicEventsSharing('public-events-token')->create();
    $otherClub = Club::factory()->withPublicEventsSharing()->create();

    Event::factory()->for($club)->create([
        'name' => 'Later event',
        'starts_at' => now()->addDays(3),
        'ends_at' => now()->addDays(3)->addHour(),
    ]);
    Event::factory()->for($club)->create([
        'name' => 'Same time first',
        'starts_at' => now()->addDays(2),
        'ends_at' => now()->addDays(2)->addHour(),
    ]);
    Event::factory()->for($club)->create([
        'name' => 'Same time second',
        'starts_at' => now()->addDays(2),
        'ends_at' => now()->addDays(2)->addHour(),
    ]);
    Event::factory()->for($club)->create([
        'name' => 'Earliest event',
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDay()->addHour(),
    ]);

    for ($number = 1; $number <= 9; $number++) {
        Event::factory()->for($club)->create([
            'name' => "Future event {$number}",
            'starts_at' => now()->addDays(10 + $number),
            'ends_at' => now()->addDays(10 + $number)->addHour(),
        ]);
    }

    Event::factory()->for($club)->create([
        'name' => 'Past event',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->subDay()->addHour(),
    ]);
    Event::factory()->for($otherClub)->create([
        'name' => 'Other club event',
        'starts_at' => now()->addHour(),
        'ends_at' => now()->addHours(2),
    ]);

    $url = route('public-events.index', $club->public_events_token);

    $this->get($url)
        ->assertSuccessful()
        ->assertSeeInOrder(['Earliest event', 'Same time first', 'Same time second', 'Later event'])
        ->assertSee('Future event 8')
        ->assertDontSee('Future event 9')
        ->assertDontSee('Past event')
        ->assertDontSee('Other club event')
        ->assertSee('name="robots" content="noindex, nofollow"', false)
        ->assertSee($url.'?page=2', false);

    $this->get(route('public-events.index', ['token' => $club->public_events_token, 'page' => 2]))
        ->assertSuccessful()
        ->assertSee('Future event 9');
});

test('the public detail page shows event information without member actions', function () {
    $club = Club::factory()->withPublicEventsSharing('public-detail-token')->create();
    $event = Event::factory()->for($club)->create([
        'name' => 'Annual meetup',
        'location' => 'Main Hall',
        'starts_at' => '2030-01-15 16:00:00',
        'ends_at' => '2030-01-15 18:00:00',
        'registration_url' => 'https://example.com/register',
        'short_description' => 'Bring coffee and snacks.',
        'image_path' => 'event-images/poster.jpg',
    ]);

    $this->get(route('public-events.show', ['token' => $club->public_events_token, 'event' => $event]))
        ->assertSuccessful()
        ->assertSee('Annual meetup')
        ->assertSee('Main Hall')
        ->assertSee('Bring coffee and snacks.')
        ->assertSee('https://example.com/register', false)
        ->assertSee($event->imageUrl(), false)
        ->assertSee(__('events.actions.add_to_calendar'))
        ->assertDontSee(__('events.actions.edit'))
        ->assertDontSee(__('events.actions.delete'))
        ->assertSee('name="robots" content="noindex, nofollow"', false);
});

test('unknown disabled past and cross-club public event requests return not found', function () {
    $enabledClub = Club::factory()->withPublicEventsSharing('enabled-token')->create();
    $disabledClub = Club::factory()->create([
        'public_events_token' => 'disabled-token',
        'public_events_enabled' => false,
    ]);
    $pastEvent = Event::factory()->for($enabledClub)->create([
        'starts_at' => now()->subDay(),
        'ends_at' => now()->subHour(),
    ]);
    $otherEvent = Event::factory()->for(Club::factory())->create();

    $this->get(route('public-events.index', 'unknown-token'))->assertNotFound();
    $this->get(route('public-events.index', $disabledClub->public_events_token))->assertNotFound();
    $this->get(route('public-events.show', ['token' => $enabledClub->public_events_token, 'event' => $pastEvent]))->assertNotFound();
    $this->get(route('public-events.show', ['token' => $enabledClub->public_events_token, 'event' => $otherEvent]))->assertNotFound();
});

test('public event pages escape user-provided content', function () {
    $club = Club::factory()->withPublicEventsSharing('escaping-token')->create([
        'name' => '<script>club()</script>',
    ]);
    $event = Event::factory()->for($club)->create([
        'name' => '<script>event()</script>',
        'location' => '<img src=x onerror=location.reload()>',
        'short_description' => '<script>description()</script>',
    ]);

    $response = $this->get(route('public-events.show', [
        'token' => $club->public_events_token,
        'event' => $event,
    ]));

    $response->assertSuccessful()
        ->assertSee(e($club->name), false)
        ->assertSee(e($event->name), false)
        ->assertSee(e($event->location), false)
        ->assertSee(e($event->short_description), false)
        ->assertDontSee($club->name, false)
        ->assertDontSee($event->name, false)
        ->assertDontSee($event->location, false)
        ->assertDontSee($event->short_description, false);
});
