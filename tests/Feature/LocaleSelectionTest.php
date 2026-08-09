<?php

use App\Models\Club;
use App\Models\Event;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('norwegian browser locales select bokmal', function (string $language) {
    $this->get(route('login'), ['Accept-Language' => $language])
        ->assertSuccessful()
        ->assertSee('<html lang="nb">', false)
        ->assertSee('Velkommen');
})->with(['nb-NO', 'nb', 'no', 'nn']);

test('unsupported browser locales fall back to english', function (string $language) {
    $this->get(route('login'), ['Accept-Language' => $language])
        ->assertSuccessful()
        ->assertSee('<html lang="en">', false)
        ->assertSee('Welcome');
})->with(['de-DE', 'fr', '']);

test('selected locale is used for authenticated layouts and dates', function () {
    $user = User::factory()->create();
    $club = Club::factory()->create();
    Member::factory()->for($club)->for($user)->create();
    $event = Event::factory()->for($club)->create([
        'starts_at' => '2030-01-15 16:00:00',
        'ends_at' => '2030-01-15 18:00:00',
    ]);

    $this->actingAs($user)
        ->get(route('clubs.events.show', [$club, $event]), ['Accept-Language' => 'nb-NO'])
        ->assertSuccessful()
        ->assertSee('<html lang="nb">', false)
        ->assertSee('jan 15, 2030 · 16:00');
});
