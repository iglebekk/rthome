<?php

use App\Enums\InvoiceDocumentType;
use App\Enums\InvoiceStatus;
use App\Models\Club;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

function invoiceIndexContext(): array
{
    $user = User::factory()->create();
    $club = Club::factory()->create();
    $member = Member::factory()->for($club)->for($user)->create();

    return [$user, $club, $member];
}

function invoiceIndexRecord(Club $club, Member $member, array $attributes = []): Invoice
{
    return Invoice::factory()->for($club)->for($member)->create($attributes);
}

it('shows all invoices by descending number by default in HTML and JSON', function () {
    [$user, $club, $member] = invoiceIndexContext();
    $older = invoiceIndexRecord($club, $member, ['number' => 10001]);
    $newer = invoiceIndexRecord($club, $member, ['number' => 10002]);

    $this->actingAs($user)
        ->get(route('clubs.invoices.index', $club))
        ->assertSeeInOrder([(string) $newer->number, (string) $older->number])
        ->assertSee(__('invoices.filters.settlement'));

    $this->actingAs($user)
        ->getJson(route('clubs.invoices.index', $club))
        ->assertOk()
        ->assertJsonPath('data.0.number', $newer->number)
        ->assertJsonPath('data.1.number', $older->number);
});

it('filters outstanding and settled invoices and exposes remaining balance', function () {
    [$user, $club, $member] = invoiceIndexContext();
    $partial = invoiceIndexRecord($club, $member, ['number' => 10001, 'paid_amount_ore' => 2500, 'paid_at' => now()]);
    invoiceIndexRecord($club, $member, ['number' => 10002]);
    $paid = invoiceIndexRecord($club, $member, ['number' => 10003, 'paid_amount_ore' => 10000, 'paid_at' => now()]);
    $credited = invoiceIndexRecord($club, $member, ['number' => 10004, 'status' => InvoiceStatus::Credited]);
    $creditNote = invoiceIndexRecord($club, $member, [
        'number' => 10005,
        'document_type' => InvoiceDocumentType::CreditNote,
        'credited_invoice_id' => $credited->getKey(),
        'gross_total_ore' => -10000,
    ]);

    $this->actingAs($user)
        ->get(route('clubs.invoices.index', [$club, 'settlement' => 'outstanding']))
        ->assertSee((string) $partial->number)
        ->assertSee('75.00')
        ->assertSee(__('invoices.statuses.partially_paid'))
        ->assertDontSee((string) $paid->number);

    $this->actingAs($user)
        ->getJson(route('clubs.invoices.index', [$club, 'settlement' => 'outstanding']))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['id' => $partial->getKey(), 'remaining_amount_ore' => 7500])
        ->assertJsonPath('data.*.id', [$member->invoices()->where('number', 10002)->value('id'), $partial->getKey()]);

    $this->actingAs($user)
        ->getJson(route('clubs.invoices.index', [$club, 'settlement' => 'settled']))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.*.id', [$credited->getKey(), $paid->getKey()]);

    $this->actingAs($user)
        ->getJson(route('clubs.invoices.index', $club))
        ->assertJsonFragment(['id' => $creditNote->getKey()]);
});

it('sorts invoices by due date and puts overdue invoices first', function () {
    [$user, $club, $member] = invoiceIndexContext();
    $future = invoiceIndexRecord($club, $member, ['number' => 10001, 'due_date' => now()->addDays(5)]);
    $overdueLater = invoiceIndexRecord($club, $member, ['number' => 10002, 'due_date' => now()->subDays(1)]);
    $overdueEarlier = invoiceIndexRecord($club, $member, ['number' => 10003, 'due_date' => now()->subDays(5)]);

    $this->actingAs($user)
        ->getJson(route('clubs.invoices.index', [$club, 'sort' => 'due_date']))
        ->assertJsonPath('data.0.id', $overdueEarlier->getKey())
        ->assertJsonPath('data.2.id', $future->getKey());

    $this->actingAs($user)
        ->getJson(route('clubs.invoices.index', [$club, 'sort' => 'overdue']))
        ->assertJsonPath('data.0.id', $overdueEarlier->getKey())
        ->assertJsonPath('data.1.id', $overdueLater->getKey())
        ->assertJsonPath('data.2.id', $future->getKey());
});

it('rejects unknown invoice filters and sorts', function () {
    [$user, $club] = invoiceIndexContext();

    $this->actingAs($user)
        ->getJson(route('clubs.invoices.index', [$club, 'settlement' => 'unknown']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('settlement');

    $this->actingAs($user)
        ->getJson(route('clubs.invoices.index', [$club, 'sort' => 'gross_total_ore']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('sort');
});

it('does not allow a user to list another clubs invoices', function () {
    [$user] = invoiceIndexContext();
    $otherClub = Club::factory()->create();

    $this->actingAs($user)
        ->get(route('clubs.invoices.index', $otherClub))
        ->assertForbidden();
});
