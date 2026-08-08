<?php

use App\Http\Controllers\EventController;
use App\Models\Club;
use App\Models\Event;
use App\Models\Member;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    Route::prefix('api/__test')->name('test.events.')->group(function (): void {
        Route::get('clubs/{club}/events', [EventController::class, 'index'])->name('index');
        Route::post('clubs/{club}/events', [EventController::class, 'store'])->name('store');
        Route::patch('clubs/{club}/events/{event}', [EventController::class, 'update'])->name('update');
        Route::delete('clubs/{club}/events/{event}', [EventController::class, 'destroy'])->name('destroy');
    });

    Route::getRoutes()->refreshNameLookups();
});

/**
 * @return array{0: User, 1: Club}
 */
function createEventClubMember(): array
{
    $user = User::factory()->create();
    $club = Club::factory()->create();

    Member::factory()->for($club)->for($user)->create();

    return [$user, $club];
}

/**
 * @return array{name: string, starts_at: string, ends_at: string}
 */
function validEventPayload(): array
{
    return [
        'name' => 'Annual meetup',
        'starts_at' => '2030-01-15T18:00:00+02:00',
        'ends_at' => '2030-01-15T20:00:00+02:00',
    ];
}

test('events belong to clubs and cast their dates', function () {
    $club = Club::factory()->create();
    $event = Event::factory()->for($club)->create();

    expect($event->club->is($club))->toBeTrue()
        ->and($club->events()->sole()->is($event))->toBeTrue()
        ->and($event->starts_at)->toBeInstanceOf(CarbonInterface::class)
        ->and($event->ends_at)->toBeInstanceOf(CarbonInterface::class)
        ->and($event->starts_at->isFuture())->toBeTrue();
});

test('the upcoming scope includes events starting now and excludes started events', function () {
    $this->travelTo(now()->startOfSecond());

    $club = Club::factory()->create();
    $started = Event::factory()->for($club)->create([
        'starts_at' => now()->subSecond(),
        'ends_at' => now()->addHour(),
    ]);
    $startingNow = Event::factory()->for($club)->create([
        'starts_at' => now(),
        'ends_at' => now()->addHour(),
    ]);
    $future = Event::factory()->for($club)->create([
        'starts_at' => now()->addHour(),
        'ends_at' => now()->addHours(2),
    ]);

    expect($club->events()->upcoming()->pluck('id')->all())
        ->toEqualCanonicalizing([$startingNow->id, $future->id])
        ->not->toContain($started->id);
});

test('a member can create an event with only required fields and dates are normalized to UTC', function () {
    [$user, $club] = createEventClubMember();

    $response = $this->actingAs($user)
        ->postJson(route('test.events.store', $club), validEventPayload());

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Annual meetup')
        ->assertJsonPath('data.starts_at', '2030-01-15T16:00:00.000000Z')
        ->assertJsonPath('data.ends_at', '2030-01-15T18:00:00.000000Z')
        ->assertJsonPath('data.image_url', null)
        ->assertJsonStructure([
            'data' => [
                'id',
                'club_id',
                'name',
                'location',
                'starts_at',
                'ends_at',
                'registration_url',
                'short_description',
                'image_url',
                'created_at',
                'updated_at',
            ],
        ]);

    $event = $club->events()->sole();

    expect($event->starts_at->toISOString())->toBe('2030-01-15T16:00:00.000000Z')
        ->and($event->ends_at->toISOString())->toBe('2030-01-15T18:00:00.000000Z')
        ->and($response->json('data'))->not->toHaveKey('image_path');
});

test('an event can be created with every optional field and an image', function () {
    Storage::fake('public');
    [$user, $club] = createEventClubMember();

    $response = $this->actingAs($user)->post(route('test.events.store', $club), [
        ...validEventPayload(),
        'location' => 'Main Hall',
        'registration_url' => 'https://example.com/register',
        'short_description' => 'A short event introduction.',
        'image' => UploadedFile::fake()->image('poster.png'),
    ], ['Accept' => 'application/json']);

    $response->assertCreated()
        ->assertJsonPath('data.location', 'Main Hall')
        ->assertJsonPath('data.registration_url', 'https://example.com/register')
        ->assertJsonPath('data.short_description', 'A short event introduction.');

    $event = $club->events()->sole();

    expect($event->image_path)->toStartWith('event-images/')
        ->and($response->json('data.image_url'))->toEndWith($event->image_path);
    Storage::disk('public')->assertExists($event->image_path);
});

test('event creation requires a name and both dates', function (array $payload, array $errors) {
    [$user, $club] = createEventClubMember();

    $this->actingAs($user)
        ->postJson(route('test.events.store', $club), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors($errors);

    expect($club->events()->count())->toBe(0);
})->with([
    'missing name' => [
        ['starts_at' => '2030-01-15T18:00:00+02:00', 'ends_at' => '2030-01-15T20:00:00+02:00'],
        ['name'],
    ],
    'missing dates' => [['name' => 'Annual meetup'], ['starts_at', 'ends_at']],
]);

test('event creation requires ISO 8601 dates with explicit offsets and an end after its start', function (array $dates, array $errors) {
    [$user, $club] = createEventClubMember();

    $this->actingAs($user)
        ->postJson(route('test.events.store', $club), ['name' => 'Annual meetup', ...$dates])
        ->assertUnprocessable()
        ->assertJsonValidationErrors($errors);
})->with([
    'missing offsets' => [[
        'starts_at' => '2030-01-15T18:00:00',
        'ends_at' => '2030-01-15T20:00:00',
    ], ['starts_at', 'ends_at']],
    'end equals start' => [[
        'starts_at' => '2030-01-15T18:00:00+02:00',
        'ends_at' => '2030-01-15T18:00:00+02:00',
    ], ['ends_at']],
    'end precedes start across offsets' => [[
        'starts_at' => '2030-01-15T18:00:00+02:00',
        'ends_at' => '2030-01-15T16:30:00+01:00',
    ], ['ends_at']],
]);

test('event text and URL fields enforce their contracts', function (array $overrides, string $error) {
    [$user, $club] = createEventClubMember();

    $this->actingAs($user)
        ->postJson(route('test.events.store', $club), [...validEventPayload(), ...$overrides])
        ->assertUnprocessable()
        ->assertJsonValidationErrors($error);
})->with([
    'long name' => [['name' => Str::repeat('a', 256)], 'name'],
    'long location' => [['location' => Str::repeat('a', 256)], 'location'],
    'invalid URL' => [['registration_url' => 'not-a-url'], 'registration_url'],
    'long URL' => [['registration_url' => 'https://example.com/'.Str::repeat('a', 2040)], 'registration_url'],
    'long description' => [['short_description' => Str::repeat('a', 501)], 'short_description'],
]);

test('event images enforce type and size restrictions', function (UploadedFile $image) {
    Storage::fake('public');
    [$user, $club] = createEventClubMember();

    $this->actingAs($user)
        ->post(route('test.events.store', $club), [...validEventPayload(), 'image' => $image], ['Accept' => 'application/json'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('image');
})->with([
    'unsupported type' => fn () => UploadedFile::fake()->create('document.pdf', 10, 'application/pdf'),
    'larger than five megabytes' => fn () => UploadedFile::fake()->create('poster.png', 5121, 'image/png'),
]);

test('the index returns only upcoming events ordered by start and paginated by 25', function () {
    $this->travelTo('2029-01-01 12:00:00');
    [$user, $club] = createEventClubMember();

    Event::factory()->for($club)->create([
        'name' => 'Already started',
        'starts_at' => now()->subMinute(),
        'ends_at' => now()->addHour(),
    ]);

    foreach (range(1, 27) as $hours) {
        Event::factory()->for($club)->create([
            'name' => "Event {$hours}",
            'starts_at' => now()->addHours($hours),
            'ends_at' => now()->addHours($hours + 1),
        ]);
    }

    $firstPage = $this->actingAs($user)
        ->getJson(route('test.events.index', $club));

    $firstPage->assertSuccessful()
        ->assertJsonCount(25, 'data')
        ->assertJsonPath('data.0.name', 'Event 1')
        ->assertJsonPath('data.24.name', 'Event 25')
        ->assertJsonPath('meta.per_page', 25)
        ->assertJsonPath('meta.total', 27);

    $this->actingAs($user)
        ->getJson(route('test.events.index', [$club, 'page' => 2]))
        ->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.name', 'Event 26')
        ->assertJsonPath('data.1.name', 'Event 27');

    expect($firstPage->json('data.*.name'))->not->toContain('Already started');
});

test('a member can update optional fields and paired dates', function () {
    [$user, $club] = createEventClubMember();
    $event = Event::factory()->for($club)->create([
        'location' => 'Old Hall',
        'registration_url' => 'https://old.example.com',
        'short_description' => 'Old description',
    ]);

    $this->actingAs($user)
        ->patchJson(route('test.events.update', [$club, $event]), [
            'name' => 'Updated meetup',
            'location' => null,
            'registration_url' => null,
            'short_description' => 'Updated description',
            'starts_at' => '2031-03-01T10:00:00-05:00',
            'ends_at' => '2031-03-01T12:30:00-05:00',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'Updated meetup')
        ->assertJsonPath('data.starts_at', '2031-03-01T15:00:00.000000Z')
        ->assertJsonPath('data.ends_at', '2031-03-01T17:30:00.000000Z');

    $event->refresh();

    expect($event->location)->toBeNull()
        ->and($event->registration_url)->toBeNull()
        ->and($event->short_description)->toBe('Updated description');
});

test('updating either event date requires both dates', function (array $payload, string $missingField) {
    [$user, $club] = createEventClubMember();
    $event = Event::factory()->for($club)->create();

    $this->actingAs($user)
        ->patchJson(route('test.events.update', [$club, $event]), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors($missingField);
})->with([
    'only start' => [['starts_at' => '2031-03-01T10:00:00+01:00'], 'ends_at'],
    'only end' => [['ends_at' => '2031-03-01T12:00:00+01:00'], 'starts_at'],
]);

test('a new event image replaces and deletes the old image', function () {
    Storage::fake('public');
    [$user, $club] = createEventClubMember();
    Storage::disk('public')->put('event-images/old.png', 'old image');
    $event = Event::factory()->for($club)->create(['image_path' => 'event-images/old.png']);

    $this->actingAs($user)
        ->patch(route('test.events.update', [$club, $event]), [
            'image' => UploadedFile::fake()->image('new.png'),
        ], ['Accept' => 'application/json'])
        ->assertSuccessful();

    $event->refresh();

    expect($event->image_path)->not->toBe('event-images/old.png');
    Storage::disk('public')->assertMissing('event-images/old.png');
    Storage::disk('public')->assertExists($event->image_path);
});

test('remove image clears and deletes the current image', function () {
    Storage::fake('public');
    [$user, $club] = createEventClubMember();
    Storage::disk('public')->put('event-images/current.png', 'current image');
    $event = Event::factory()->for($club)->create(['image_path' => 'event-images/current.png']);

    $this->actingAs($user)
        ->patchJson(route('test.events.update', [$club, $event]), ['remove_image' => true])
        ->assertSuccessful()
        ->assertJsonPath('data.image_url', null);

    expect($event->refresh()->image_path)->toBeNull();
    Storage::disk('public')->assertMissing('event-images/current.png');
});

test('a new image and remove image cannot be submitted together', function () {
    Storage::fake('public');
    [$user, $club] = createEventClubMember();
    $event = Event::factory()->for($club)->create();

    $this->actingAs($user)
        ->patch(route('test.events.update', [$club, $event]), [
            'image' => UploadedFile::fake()->image('new.png'),
            'remove_image' => true,
        ], ['Accept' => 'application/json'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('image');
});

test('deleting an event deletes its image and returns no content', function () {
    Storage::fake('public');
    [$user, $club] = createEventClubMember();
    Storage::disk('public')->put('event-images/poster.png', 'poster');
    $event = Event::factory()->for($club)->create(['image_path' => 'event-images/poster.png']);

    $this->actingAs($user)
        ->deleteJson(route('test.events.destroy', [$club, $event]))
        ->assertNoContent();

    $this->assertModelMissing($event);
    Storage::disk('public')->assertMissing('event-images/poster.png');
});

test('deleting a club cascades events and deletes all event images', function () {
    Storage::fake('public');
    $club = Club::factory()->create();
    Storage::disk('public')->put('event-images/first.png', 'first');
    Storage::disk('public')->put('event-images/second.png', 'second');
    $events = collect([
        Event::factory()->for($club)->create(['image_path' => 'event-images/first.png']),
        Event::factory()->for($club)->create(['image_path' => 'event-images/second.png']),
    ]);

    $club->delete();

    $events->each(fn (Event $event) => $this->assertModelMissing($event));
    Storage::disk('public')->assertMissing(['event-images/first.png', 'event-images/second.png']);
});

test('club members can manage events while outsiders cannot', function () {
    [$member, $club] = createEventClubMember();
    $outsider = User::factory()->create();
    $event = Event::factory()->for($club)->create();

    expect($member->can('create', [Event::class, $club]))->toBeTrue()
        ->and($member->can('update', $event))->toBeTrue()
        ->and($member->can('delete', $event))->toBeTrue()
        ->and($outsider->can('create', [Event::class, $club]))->toBeFalse()
        ->and($outsider->can('update', $event))->toBeFalse()
        ->and($outsider->can('delete', $event))->toBeFalse();

    $this->actingAs($outsider)
        ->getJson(route('test.events.index', $club))
        ->assertNotFound();

    $this->actingAs($outsider)
        ->postJson(route('test.events.store', $club), validEventPayload())
        ->assertNotFound();

    $this->actingAs($outsider)
        ->patchJson(route('test.events.update', [$club, $event]), ['name' => 'Forbidden'])
        ->assertNotFound();

    $this->actingAs($outsider)
        ->deleteJson(route('test.events.destroy', [$club, $event]))
        ->assertNotFound();

    $this->assertModelExists($event);
});

test('guests cannot access event endpoints', function () {
    $club = Club::factory()->create();
    $event = Event::factory()->for($club)->create();

    $this->getJson(route('test.events.index', $club))->assertForbidden();
    $this->postJson(route('test.events.store', $club), validEventPayload())->assertForbidden();
    $this->patchJson(route('test.events.update', [$club, $event]), ['name' => 'Forbidden'])->assertForbidden();
    $this->deleteJson(route('test.events.destroy', [$club, $event]))->assertForbidden();
});

test('an event cannot be accessed through another club', function () {
    [$user, $firstClub] = createEventClubMember();
    $secondClub = Club::factory()->create();
    $event = Event::factory()->for($secondClub)->create();

    $this->actingAs($user)
        ->patchJson(route('test.events.update', [$firstClub, $event]), ['name' => 'Cross-club update'])
        ->assertNotFound();

    $this->actingAs($user)
        ->deleteJson(route('test.events.destroy', [$firstClub, $event]))
        ->assertNotFound();

    $this->assertModelExists($event);
});
