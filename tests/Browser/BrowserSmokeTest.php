<?php

use App\Models\Club;
use App\Models\ClubInvitation;
use App\Models\Member;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('the sign in flow is clear in light desktop mode and keyboard reachable', function () {
    visit(route('login'))
        ->inLightMode()
        ->on()->desktop()
        ->assertSee(__('auth.identify.title'))
        ->keys('email', 'Tab')
        ->assertScript('document.activeElement !== document.body')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

test('the sign in flow respects dark system preference on mobile', function () {
    visit(route('login'))
        ->inDarkMode()
        ->on()->mobile()
        ->assertSee(__('auth.identify.title'))
        ->assertScript('window.matchMedia("(prefers-color-scheme: dark)").matches')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

test('a member can sign in through both account steps and reach the dashboard', function () {
    $user = User::factory()->create(['password' => 'password']);
    $club = Club::factory()->create();
    Member::factory()->for($club)->for($user)->create(['email' => $user->email]);

    visit(route('login'))
        ->fill('email', $user->email)
        ->click(__('auth.identify.submit'))
        ->assertSee(__('auth.password_step.title'))
        ->fill('password', 'password')
        ->click(__('auth.password_step.submit'))
        ->assertSee(__('dashboard.description'))
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

test('the dashboard quick actions are available in a dropdown', function () {
    $user = User::factory()->create();
    $club = Club::factory()->create();
    Member::factory()->for($club)->for($user)->create();

    $this->actingAs($user);

    visit(route('clubs.dashboard', $club))
        ->inLightMode()
        ->assertSee(__('dashboard.quick_actions'))
        ->assertScript('! document.querySelector(\'[data-test="dashboard-secondary-action"]\')')
        ->click(__('dashboard.quick_actions'))
        ->assertSee(__('members.actions.create'))
        ->assertSee(__('events.actions.create'))
        ->assertSee(__('links.actions.create'))
        ->assertScript('document.querySelector(\'[data-test="dashboard-quick-action-member"]\').href === '.json_encode(route('clubs.members.create', $club)))
        ->assertScript('document.querySelector(\'[data-test="dashboard-quick-action-event"]\').href === '.json_encode(route('clubs.events.create', $club)))
        ->assertScript('document.querySelector(\'[data-test="dashboard-quick-action-link"]\').href === '.json_encode(route('clubs.links.create', $club)))
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

test('a member can open the club settings submenu', function () {
    $user = User::factory()->create();
    $club = Club::factory()->create();
    Member::factory()->for($club)->for($user)->create();

    $this->actingAs($user);

    visit(route('clubs.dashboard', $club))
        ->assertScript("getComputedStyle(document.querySelector('[data-test=\"club-settings-menu\"]')).display === 'none'")
        ->click(__('app.navigation.settings'))
        ->assertSee(__('app.navigation.club_details'))
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

test('a member can copy an active invitation link', function () {
    $user = User::factory()->create();
    $club = Club::factory()->create();
    Member::factory()->for($club)->for($user)->create();
    ClubInvitation::factory()->for($club)->for($user, 'createdBy')->create();

    $this->actingAs($user);

    visit(route('clubs.settings.invitations', $club))
        ->click(__('clubs.settings.invitations.copy'))
        ->wait(1)
        ->assertSee(__('clubs.settings.invitations.copied'))
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

test('the dashboard positions grid adapts between desktop and mobile widths', function () {
    $user = User::factory()->create();
    $club = Club::factory()->create();
    $member = Member::factory()->for($club)->for($user)->create();

    foreach (range(1, 2) as $sortOrder) {
        Position::factory()->for($club)->for($member)->create([
            'name' => "Dashboard position {$sortOrder}",
            'sort_order' => $sortOrder,
        ]);
    }

    $this->actingAs($user);

    visit(route('clubs.dashboard', $club))
        ->on()->desktop()
        ->assertScript("document.querySelector('[data-test=\"dashboard-positions-grid\"]').className.includes('grid-cols-[repeat(auto-fit,minmax(200px,280px))]')")
        ->assertScript("getComputedStyle(document.querySelector('[data-test=\"dashboard-positions-grid\"]')).gridTemplateColumns.split(' ').length > 1")
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();

    visit(route('clubs.dashboard', $club))
        ->on()->mobile()
        ->assertScript("getComputedStyle(document.querySelector('[data-test=\"dashboard-positions-grid\"]')).gridTemplateColumns.split(' ').length === 1")
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});
