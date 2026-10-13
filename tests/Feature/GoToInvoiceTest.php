<?php

use App\Enums\InvoiceStatus;
use App\Models\Club;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

function goToInvoiceContext(): array
{
    $user = User::factory()->create();
    $club = Club::factory()->create();
    Member::factory()->for($club)->for($user)->create();

    return [$user, $club];
}

test('the invoice overview renders the go to invoice control', function () {
    [$user, $club] = goToInvoiceContext();

    $response = $this->actingAs($user)
        ->get(route('clubs.invoices.index', $club));

    $response->assertSuccessful()
        ->assertSee(__('invoices.go_to.open'))
        ->assertSee(__('invoices.go_to.number'));
});

test('invoice lookup starts at three digits and finds invoices regardless of status', function () {
    [$user, $club] = goToInvoiceContext();
    $invoice = Invoice::factory()->for($club)->create([
        'number' => 12345,
        'status' => InvoiceStatus::Credited,
    ]);

    Livewire::actingAs($user)
        ->test('invoices.go-to-invoice', ['clubId' => $club->getKey()])
        ->set('invoiceNumber', '12')
        ->assertDontSee(__('invoices.go_to.not_found'))
        ->set('invoiceNumber', '12345')
        ->assertSee(__('invoices.go_to.found', ['number' => '12345']))
        ->call('goToInvoice')
        ->assertRedirect(route('clubs.invoices.show', [$club, $invoice]));

    expect($invoice->refresh()->status)->toBe(InvoiceStatus::Credited);
});

test('invoice lookup does not reveal invoices from a different accessible club', function () {
    [$user, $club] = goToInvoiceContext();
    $otherClub = Club::factory()->create();
    Member::factory()->for($otherClub)->for($user)->create();
    $invoice = Invoice::factory()->for($otherClub)->create(['number' => 12345]);

    Livewire::actingAs($user)
        ->test('invoices.go-to-invoice', ['clubId' => $club->getKey()])
        ->set('invoiceNumber', '12345')
        ->assertSee(__('invoices.go_to.not_found'))
        ->call('goToInvoice')
        ->assertNoRedirect();

    expect($invoice->exists)->toBeTrue();
});

test('the active club cannot be changed through Livewire state', function () {
    [$user, $club] = goToInvoiceContext();
    $otherClub = Club::factory()->create();
    Member::factory()->for($otherClub)->for($user)->create();

    $component = Livewire::actingAs($user)
        ->test('invoices.go-to-invoice', ['clubId' => $club->getKey()]);

    expect(fn () => $component->set('clubId', $otherClub->getKey()))
        ->toThrow(CannotUpdateLockedPropertyException::class);
});

test('club access is checked again when opening a previously found invoice', function () {
    [$user, $club] = goToInvoiceContext();
    $membership = $user->members()->where('club_id', $club->getKey())->firstOrFail();
    Invoice::factory()->for($club)->create(['number' => 12345]);

    $component = Livewire::actingAs($user)
        ->test('invoices.go-to-invoice', ['clubId' => $club->getKey()])
        ->set('invoiceNumber', '12345');

    $membership->delete();

    expect($user->fresh()->clubs()->whereKey($club)->exists())->toBeFalse();

    $component->call('goToInvoice')->assertStatus(404)->assertNoRedirect();
});

test('invoice lookup is disabled when the number is not confirmed', function () {
    [$user, $club] = goToInvoiceContext();
    $invoice = Invoice::factory()->for($club)->create(['number' => 12345]);

    Livewire::actingAs($user)
        ->test('invoices.go-to-invoice', ['clubId' => $club->getKey()])
        ->call('goToInvoice')
        ->assertNoRedirect()
        ->set('invoiceNumber', '12345')
        ->call('goToInvoice')
        ->assertRedirect(route('clubs.invoices.show', [$club, $invoice]))
        ->set('invoiceNumber', '99999')
        ->call('goToInvoice')
        ->assertNoRedirect();
});

test('closing the modal resets the invoice lookup', function () {
    [$user, $club] = goToInvoiceContext();
    Invoice::factory()->for($club)->create(['number' => 12345]);

    Livewire::actingAs($user)
        ->test('invoices.go-to-invoice', ['clubId' => $club->getKey()])
        ->set('invoiceNumber', '12345')
        ->assertSet('invoiceExists', true)
        ->call('resetLookup')
        ->assertSet('invoiceNumber', '')
        ->assertSet('invoiceExists', false)
        ->assertDontSee(__('invoices.go_to.found', ['number' => '12345']));
});
