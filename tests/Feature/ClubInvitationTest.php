<?php

use App\Models\Club;
use App\Models\ClubInvitation;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

function invitationContext(): array
{
    $user = User::factory()->create();
    $club = Club::factory()->create();
    Member::factory()->for($club)->for($user)->create(['email' => $user->email]);

    return [$user, $club];
}

test('club members can create invitations with every supported lifetime', function (int $days) {
    [$user, $club] = invitationContext();

    $this->actingAs($user)->post(route('clubs.settings.invitations.store', $club), [
        'name' => 'Open day',
        'days' => $days,
    ])->assertRedirectToRoute('clubs.settings.invitations', $club);

    expect((int) round(abs(ClubInvitation::query()->sole()->expires_at->diffInDays(now()))))->toBe($days);
})->with([1, 7, 30, 90]);

test('an existing verified user can join from an invitation without duplicate membership', function () {
    [$owner, $club] = invitationContext();
    $token = 'known-invitation-token';
    $invitation = ClubInvitation::factory()->for($club)->for($owner, 'createdBy')->create([
        'token_hash' => hash('sha256', $token),
    ]);
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('club-invitations.show', $token))->assertSuccessful()->assertSee($club->name);
    $this->actingAs($user)->post(route('club-invitations.confirm', $token))->assertRedirectToRoute('clubs.dashboard', $club);
    $this->actingAs($user)->post(route('club-invitations.confirm', $token))->assertRedirectToRoute('clubs.dashboard', $club);

    expect($club->members()->where('user_id', $user->id)->count())->toBe(1)
        ->and($invitation->refresh()->isUsable())->toBeTrue();
});

test('a guest invitation uses the email identification flow', function () {
    [$owner, $club] = invitationContext();
    $token = 'guest-invitation-token';
    ClubInvitation::factory()->for($club)->for($owner, 'createdBy')->create([
        'token_hash' => hash('sha256', $token),
    ]);

    $this->get(route('club-invitations.show', $token))
        ->assertSuccessful()
        ->assertSee(__('auth.identify.email'))
        ->assertSee(__('auth.identify.submit'))
        ->assertDontSee(__('clubs.settings.invitations.sign_in'))
        ->assertDontSee(__('clubs.settings.invitations.register'));
});

test('invalid invitation states are rejected', function (array $attributes) {
    $token = 'invalid-invitation-token';
    $invitation = ClubInvitation::factory()->create(array_merge([
        'token_hash' => hash('sha256', $token),
    ], $attributes));

    $this->get(route('club-invitations.show', $token))->assertNotFound();
    expect($invitation->exists)->toBeTrue();
})->with([
    'expired' => [['expires_at' => now()->subMinute()]],
    'revoked' => [['revoked_at' => now()]],
]);

test('only members can manage a clubs invitations', function () {
    [$owner, $club] = invitationContext();
    $otherUser = User::factory()->create();
    $invitation = ClubInvitation::factory()->for($club)->for($owner, 'createdBy')->create();

    $this->actingAs($otherUser)->get(route('clubs.settings.invitations', $club))->assertNotFound();
    $this->actingAs($otherUser)->delete(route('club-invitations.destroy', $invitation))->assertForbidden();
});

test('an invitation cannot create an account for an unknown email', function () {
    $owner = User::factory()->create();
    $club = Club::factory()->create();
    Member::factory()->for($club)->for($owner)->create();
    $token = 'unknown-member-invitation-token';
    ClubInvitation::factory()->for($club)->for($owner, 'createdBy')->create([
        'token_hash' => hash('sha256', $token),
    ]);

    $this->get(route('club-invitations.show', $token))->assertSuccessful();

    $this->post(route('login.identify'), ['email' => 'new-member@example.com'])
        ->assertSessionHasErrors([
            'email' => __('auth.identify.unavailable'),
        ]);

    $this->get('/register')->assertNotFound();
});

test('the invitation settings page shows links and controls', function () {
    [$user, $club] = invitationContext();
    ClubInvitation::factory()->for($club)->for($user, 'createdBy')->create();

    $this->actingAs($user)->get(route('clubs.settings.invitations', $club))
        ->assertSuccessful()
        ->assertSee(__('app.navigation.invitations'))
        ->assertSee(__('clubs.settings.invitations.copy'))
        ->assertSee(__('clubs.settings.invitations.deactivate'));
});

test('a token invitation link can be opened and accepted', function () {
    [$owner, $club] = invitationContext();
    $token = hash('sha256', 'token-invitation');
    ClubInvitation::factory()->for($club)->for($owner, 'createdBy')->create(['token_hash' => $token]);
    $user = User::factory()->create();
    $url = route('club-invitations.show', ['token' => $token]);

    $this->actingAs($user)->get($url)->assertSuccessful()->assertSee($club->name);
    $this->actingAs($user)->post(route('club-invitations.confirm', $token))->assertRedirectToRoute('clubs.dashboard', $club);
});
