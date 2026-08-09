<?php

use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\Club;
use App\Models\Member;
use App\Models\MemberActivation;
use App\Models\User;
use App\Notifications\MemberActivationNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

uses(LazilyRefreshDatabase::class);

test('account identification continues to password for an existing user', function () {
    $user = User::factory()->create(['email' => 'member@example.com']);

    $this->post(route('login.identify'), ['email' => ' MEMBER@example.com '])
        ->assertRedirectToRoute('login')
        ->assertSessionHas('login.email', $user->email);

    $this->get(route('login'))
        ->assertSuccessful()
        ->assertSee($user->email)
        ->assertSee(__('auth.password_step.title'));
});

test('account identification sends an activation for an unclaimed membership', function () {
    Notification::fake();
    Member::factory()->create(['email' => 'waiting@example.com', 'user_id' => null]);

    $this->post(route('login.identify'), ['email' => 'waiting@example.com'])
        ->assertRedirectToRoute('activation.sent');

    $this->assertDatabaseHas('member_activations', ['email' => 'waiting@example.com', 'used_at' => null]);
    Notification::assertSentOnDemand(MemberActivationNotification::class);
});

test('an unknown email stays on identification because registration is disabled', function () {
    $this->post(route('login.identify'), ['email' => 'new@example.com'])
        ->assertSessionHasErrors([
            'email' => __('auth.identify.unavailable'),
        ]);

    $this->get('/register')->assertNotFound();
});

test('a signed activation creates one verified account and links every matching membership', function () {
    Notification::fake();
    $firstClub = Club::factory()->create();
    $secondClub = Club::factory()->create();
    Member::factory()->for($firstClub)->create(['email' => 'claim@example.com']);
    Member::factory()->for($secondClub)->create(['email' => 'claim@example.com']);

    $this->post(route('login.identify'), ['email' => 'claim@example.com']);

    $activation = MemberActivation::query()->sole();
    $activationUrl = null;
    Notification::assertSentOnDemand(
        MemberActivationNotification::class,
        function (MemberActivationNotification $notification) use (&$activationUrl): bool {
            $activationUrl = $notification->toMail((object) [])->actionUrl;

            return true;
        },
    );

    $this->get($activationUrl)
        ->assertSuccessful()
        ->assertSee(__('auth.activation.title'));

    $this->post($activationUrl, [
        'name' => 'Claimed Member',
        'password' => 'secure-password',
        'password_confirmation' => 'secure-password',
    ])->assertRedirectToRoute('home');

    $user = User::query()->where('email', 'claim@example.com')->sole();

    expect($user->hasVerifiedEmail())->toBeTrue()
        ->and($user->clubs()->pluck('clubs.id')->all())->toEqualCanonicalizing([$firstClub->id, $secondClub->id])
        ->and($activation->refresh()->used_at)->not->toBeNull();

    $this->post(route('logout'));
    $this->get($activationUrl)->assertNotFound();
});

test('expired activation links are rejected by signed middleware', function () {
    $activation = MemberActivation::factory()->create(['expires_at' => now()->subMinute()]);
    $url = URL::temporarySignedRoute('activation.show', now()->subMinute(), [
        'activation' => $activation,
        'token' => 'expired-token',
    ]);

    $this->get($url)->assertForbidden();
});

test('verification links new memberships and synchronizes a changed profile email', function () {
    Event::fakeExcept(Verified::class);
    $user = User::factory()->create(['email' => 'old@example.com']);
    $club = Club::factory()->create();
    $linkedMember = Member::factory()->for($club)->for($user)->create(['email' => 'old@example.com']);
    $otherClub = Club::factory()->create();
    $unclaimedMember = Member::factory()->for($otherClub)->create(['email' => 'new@example.com']);

    app(UpdateUserProfileInformation::class)->update($user, [
        'name' => 'Updated Name',
        'email' => 'NEW@example.com',
    ]);

    expect($user->refresh()->hasVerifiedEmail())->toBeFalse();

    $user->forceFill(['email_verified_at' => now()])->save();
    event(new Verified($user));

    expect($linkedMember->refresh()->email)->toBe('new@example.com')
        ->and($unclaimedMember->refresh()->user_id)->toBe($user->id);
});
