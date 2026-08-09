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
function createImportContext(): array
{
    $user = User::factory()->create();
    $club = Club::factory()->create();

    Member::factory()->for($club)->for($user)->create();

    return [$user, $club];
}

function importJson(array $events): string
{
    return json_encode(['events' => $events], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
}

test('a member can import one or more events with optional fields', function () {
    [$user, $club] = createImportContext();

    $response = $this->actingAs($user)->post(route('clubs.settings.events.import', $club), [
        'json' => importJson([
            [
                'name' => 'Annual meetup',
                'location' => 'Main Hall',
                'starts_at' => '2030-01-15T18:00:00+01:00',
                'ends_at' => '2030-01-15T20:00:00+01:00',
                'registration_url' => 'https://example.com/register',
                'short_description' => 'A short event introduction.',
            ],
            [
                'name' => 'Community lunch',
                'starts_at' => '2030-02-15T12:00:00Z',
                'ends_at' => '2030-02-15T13:00:00Z',
            ],
        ]),
    ]);

    $response->assertRedirectToRoute('clubs.settings.events', $club)
        ->assertSessionHas('status', '2 events imported.');

    $events = $club->events()->orderBy('name')->get();

    expect($events)->toHaveCount(2)
        ->and($events->first()->location)->toBe('Main Hall')
        ->and($events->first()->starts_at->toISOString())->toBe('2030-01-15T17:00:00.000000Z')
        ->and($events->first()->ends_at->toISOString())->toBe('2030-01-15T19:00:00.000000Z')
        ->and($events->last()->location)->toBeNull();
});

test('an import rejects invalid JSON and invalid event fields without creating events', function (array $payload, array $errors) {
    [$user, $club] = createImportContext();

    $this->actingAs($user)
        ->post(route('clubs.settings.events.import', $club), ['json' => $payload['json']])
        ->assertSessionHasErrors($errors);

    expect($club->events()->count())->toBe(0);
})->with([
    'invalid JSON' => [['json' => '{not valid'], ['json']],
    'missing event name' => [['json' => importJson([[
        'starts_at' => '2030-01-15T18:00:00+01:00',
        'ends_at' => '2030-01-15T20:00:00+01:00',
    ]])], ['events.0.name']],
    'missing timezone' => [['json' => importJson([[
        'name' => 'Annual meetup',
        'starts_at' => '2030-01-15T18:00:00',
        'ends_at' => '2030-01-15T20:00:00',
    ]])], ['events.0.starts_at', 'events.0.ends_at']],
    'end before start' => [['json' => importJson([[
        'name' => 'Annual meetup',
        'starts_at' => '2030-01-15T18:00:00+01:00',
        'ends_at' => '2030-01-15T17:00:00+01:00',
    ]])], ['events.0.ends_at']],
]);

test('an import is limited to 100 events', function () {
    [$user, $club] = createImportContext();
    $events = array_map(fn (int $index): array => [
        'name' => "Event {$index}",
        'starts_at' => '2030-01-15T18:00:00+01:00',
        'ends_at' => '2030-01-15T20:00:00+01:00',
    ], range(1, 101));

    $this->actingAs($user)
        ->post(route('clubs.settings.events.import', $club), ['json' => importJson($events)])
        ->assertSessionHasErrors('events');

    expect($club->events()->count())->toBe(0);
});

test('a user without club access cannot import events', function () {
    $user = User::factory()->create();
    $club = Club::factory()->create();

    $this->actingAs($user)
        ->post(route('clubs.settings.events.import', $club), ['json' => importJson([])])
        ->assertNotFound();

    expect(Event::query()->count())->toBe(0);
});

test('the event settings page shows import instructions and example', function () {
    [$user, $club] = createImportContext();

    $this->actingAs($user)
        ->get(route('clubs.settings.events', $club))
        ->assertSuccessful()
        ->assertSee(__('clubs.settings.events.import.agent_prompt_title'))
        ->assertSee(__('clubs.settings.events.import.agent_prompt'))
        ->assertSee(__('clubs.settings.events.import.title'))
        ->assertSee(__('clubs.settings.events.import.timezone'))
        ->assertDontSee(__('clubs.settings.events.import.example_title'));
});
