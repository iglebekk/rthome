<?php

namespace App\Actions;

use App\Enums\InvoiceCreationStatus;
use App\Enums\InvoiceDocumentType;
use App\Enums\InvoiceStatus;
use App\Models\Club;
use App\Models\InvoiceCreation;
use App\Models\InvoiceLine;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateInvoicesAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Club $club, array $data, ?InvoiceCreation $creation = null): InvoiceCreation
    {
        return DB::transaction(function () use ($club, $data, $creation): InvoiceCreation {
            $lockedClub = Club::query()->whereKey($club->getKey())->lockForUpdate()->firstOrFail();
            $creation = $creation === null
                ? $lockedClub->invoiceCreations()->where('submission_token', $data['submission_token'])->first()
                : $lockedClub->invoiceCreations()->whereKey($creation->getKey())->firstOrFail();

            if ($creation?->status === InvoiceCreationStatus::Issued) {
                return $creation->load(['invoices.lines']);
            }

            $recipientIds = array_values($data['recipients'] ?? []);
            $lines = array_values($data['lines'] ?? []);
            $productIds = collect($lines)->pluck('product_id')->unique()->values();
            $members = $lockedClub->members()->whereIn('id', $recipientIds)->get()->keyBy('id');
            $products = $lockedClub->products()->whereIn('id', $productIds)->get()->keyBy('id');

            if ($members->count() !== count($recipientIds)) {
                throw ValidationException::withMessages(['recipients' => __('invoices.validation.recipients_club')]);
            }

            if ($products->count() !== $productIds->count()) {
                throw ValidationException::withMessages(['lines' => __('invoices.validation.products_club')]);
            }

            $creation ??= $lockedClub->invoiceCreations()->create([
                'submission_token' => $data['submission_token'],
                'status' => InvoiceCreationStatus::Draft,
            ]);

            $creation->update([
                'invoice_date' => $data['invoice_date'] ?? null,
                'due_date' => $data['due_date'] ?? null,
            ]);
            $creation->recipients()->sync($recipientIds);
            $creation->lines()->delete();
            $creation->lines()->createMany(
                collect($lines)->map(fn (array $line, int $position): array => [
                    'product_id' => $line['product_id'],
                    'quantity' => $line['quantity'],
                    'position' => $position,
                ])->all(),
            );

            if ($data['intent'] === 'draft') {
                return $creation->load(['recipients', 'lines.product']);
            }

            if ($products->contains(fn (Product $product): bool => ! $product->is_active)) {
                throw ValidationException::withMessages(['lines' => __('invoices.validation.products_active')]);
            }

            $lineValues = $this->buildLines($lines, $products);
            $nextNumber = (int) $lockedClub->invoice_sequence;

            foreach ($recipientIds as $memberId) {
                $member = $members->get($memberId);
                $nextNumber++;
                $invoice = $lockedClub->invoices()->create([
                    'invoice_creation_id' => $creation->getKey(),
                    'member_id' => $member->getKey(),
                    'document_type' => InvoiceDocumentType::Invoice,
                    'status' => InvoiceStatus::Issued,
                    'number' => $nextNumber,
                    'invoice_date' => $data['invoice_date'],
                    'due_date' => $data['due_date'],
                    'issued_at' => now(),
                    'club_name' => $lockedClub->name,
                    'club_organization_number' => $lockedClub->organization_number,
                    'club_account_number' => $lockedClub->account_number,
                    'recipient_name' => $member->invoice_company_name ?: $member->name,
                    'recipient_company_name' => $member->invoice_company_name,
                    'recipient_organization_number' => $member->invoice_organization_number,
                    'recipient_address' => $member->invoice_address,
                    'recipient_postal_code' => $member->invoice_postal_code,
                    'recipient_city' => $member->invoice_city,
                    'recipient_email' => $member->email,
                    'net_total_ore' => $lineValues['net_total_ore'],
                    'vat_total_ore' => $lineValues['vat_total_ore'],
                    'gross_total_ore' => $lineValues['gross_total_ore'],
                ]);

                InvoiceLine::withoutEvents(fn (): Collection => $invoice->lines()->createMany($lineValues['lines']));
            }

            $lockedClub->update(['invoice_sequence' => $nextNumber]);
            $creation->update([
                'status' => InvoiceCreationStatus::Issued,
                'issued_at' => now(),
            ]);

            return $creation->load(['invoices.lines']);
        }, attempts: 3);
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @param  Collection<int, Product>  $products
     * @return array{lines: list<array<string, mixed>>, net_total_ore: int, vat_total_ore: int, gross_total_ore: int}
     */
    private function buildLines(array $lines, Collection $products): array
    {
        $invoiceLines = [];
        $netTotal = 0;
        $vatTotal = 0;
        $grossTotal = 0;

        foreach ($lines as $position => $line) {
            $product = $products->get($line['product_id']);
            $quantityHundredths = $this->quantityHundredths((string) $line['quantity']);
            $grossAmount = intdiv(($product->gross_price_ore * $quantityHundredths) + 50, 100);
            $rate = $product->vat_treatment->rate();
            $netAmount = $rate === 0
                ? $grossAmount
                : intdiv(($grossAmount * 100) + intdiv(100 + $rate, 2), 100 + $rate);
            $vatAmount = $grossAmount - $netAmount;

            $invoiceLines[] = [
                'product_id' => $product->getKey(),
                'position' => $position,
                'description' => filled($product->description) ? $product->description : $product->name,
                'quantity' => $line['quantity'],
                'gross_unit_price_ore' => $product->gross_price_ore,
                'vat_treatment' => $product->vat_treatment,
                'net_amount_ore' => $netAmount,
                'vat_amount_ore' => $vatAmount,
                'gross_amount_ore' => $grossAmount,
            ];
            $netTotal += $netAmount;
            $vatTotal += $vatAmount;
            $grossTotal += $grossAmount;
        }

        return [
            'lines' => $invoiceLines,
            'net_total_ore' => $netTotal,
            'vat_total_ore' => $vatTotal,
            'gross_total_ore' => $grossTotal,
        ];
    }

    private function quantityHundredths(string $quantity): int
    {
        [$whole, $fraction] = array_pad(explode('.', $quantity, 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }
}
