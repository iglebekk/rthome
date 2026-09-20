<?php

use App\Models\Club;
use App\Models\ClubInvitation;
use App\Models\Member;
use App\Models\Position;
use App\Models\Product;
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
        ->assertScript("getComputedStyle(document.querySelector('[data-test=\"dashboard-quick-actions-menu\"]')).display !== 'none'")
        ->assertScript("document.querySelector('[data-test=\"dashboard-quick-action-member\"]') !== null")
        ->assertScript("document.querySelector('[data-test=\"dashboard-quick-action-event\"]') !== null")
        ->assertScript("document.querySelector('[data-test=\"dashboard-quick-action-link\"]') !== null")
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

test('a Brreg network failure shows a localized error', function () {
    $user = User::factory()->create();
    $club = Club::factory()->create();
    Member::factory()->for($club)->for($user)->create();

    $this->actingAs($user);

    visit(route('clubs.members.create', $club))
        ->assertScript('window.fetch = () => Promise.reject(new TypeError("Failed to fetch")); true')
        ->fill('invoice_organization_number', '987654321')
        ->click(__('members.invoice.lookup'))
        ->assertSee(__('members.invoice.lookup_unavailable'))
        ->assertDontSee('Failed to fetch')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

test('invoice creation updates recipients products totals and submit text without JavaScript errors', function () {
    $user = User::factory()->create();
    $club = Club::factory()->create(['organization_number' => '912345678']);
    $firstMember = Member::factory()->for($club)->for($user)->create(['name' => 'First Member']);
    $secondMember = Member::factory()->for($club)->create(['name' => 'Second Member']);
    $product = Product::factory()->for($club)->create([
        'name' => 'Annual membership',
        'gross_price_ore' => 50000,
    ]);
    $secondProduct = Product::factory()->for($club)->create([
        'name' => 'Workshop ticket',
        'gross_price_ore' => 25000,
    ]);

    $this->actingAs($user);

    visit(route('clubs.invoice-creations.create', $club))
        ->click(__('invoices.actions.add_recipient'))
        ->click($firstMember->name)
        ->click(__('invoices.actions.add_selected'))
        ->click(__('invoices.actions.add_recipient'))
        ->click($secondMember->name)
        ->click(__('invoices.actions.add_selected'))
        ->assertScript("document.querySelectorAll('[data-invoice-recipient]').length === 2")
        ->assertSee(__('invoices.actions.issue_many', ['count' => 2]))
        ->assertScript(
            "(() => { document.querySelector('[data-modal-trigger=invoice-products]').click(); return true; })()",
        )
        ->assertScript("(() => { document.querySelector('[data-picker-option=\"product\"][data-picker-id=\"{$product->getKey()}\"]').click(); document.querySelector('[data-picker-option=\"product\"][data-picker-id=\"{$secondProduct->getKey()}\"]').click(); return true; })()")
        ->assertScript("(() => { document.querySelector('[data-add-selected-products]').click(); return true; })()")
        ->assertScript("document.querySelectorAll('[data-invoice-line]').length === 2")
        ->assertScript(
            "(() => { const quantity = document.querySelector('[data-line-quantity]'); quantity.value = '2'; quantity.dispatchEvent(new Event('input', { bubbles: true })); return document.querySelector('[data-invoice-total]').textContent.includes('1,250') || document.querySelector('[data-invoice-total]').textContent.includes('1 250'); })()",
        )
        ->assertScript("(() => { document.querySelectorAll('[data-remove-invoice-line]')[1].click(); document.querySelectorAll('[data-remove-invoice-recipient]')[1].click(); return document.querySelectorAll('[data-invoice-line]').length === 1 && document.querySelectorAll('[data-invoice-recipient]').length === 1; })()")
        ->assertSee(__('invoices.actions.issue_one'))
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
