<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\Facades\Pdf;

class InvoicePdfService
{
    private const DISK = 'local';

    public function generate(Invoice $invoice): string
    {
        $path = $this->path($invoice);
        $disk = Storage::disk(self::DISK);

        if ($disk->exists($path)) {
            return $path;
        }

        $disk->makeDirectory(dirname($path));

        Pdf::view('pdfs.invoice', [
            'document' => $this->document($invoice),
        ])
            ->format('a4')
            ->name($this->filename($invoice))
            ->save($disk->path($path));

        return $path;
    }

    public function path(Invoice $invoice): string
    {
        $number = $invoice->number ?? $invoice->getKey();

        return sprintf('invoices/%s/%s.pdf', $invoice->getAttribute('club_id'), $number);
    }

    public function absolutePath(Invoice $invoice): string
    {
        return Storage::disk(self::DISK)->path($this->path($invoice));
    }

    public function filename(Invoice $invoice): string
    {
        return sprintf('faktura %s.pdf', $invoice->number ?? $invoice->getKey());
    }

    /**
     * @return array{
     *     number: int|string,
     *     document_type: string,
     *     invoice_date: string,
     *     due_date: string,
     *     seller_name: string,
     *     seller_organization_number: string,
     *     recipient_name: string,
     *     recipient_company_name: ?string,
     *     recipient_organization_number: ?string,
     *     recipient_address: ?string,
     *     recipient_postal_code: ?string,
     *     recipient_city: ?string,
     *     lines: array<int, array{description: string, quantity: string, unit_price: string, net_amount: string, vat_amount: string, gross_amount: string, vat_treatment: string}>,
     *     net_total: string,
     *     vat_total: string,
     *     gross_total: string,
     * }
     */
    public function document(Invoice $invoice): array
    {
        $lines = $invoice->loadMissing('lines')->lines;

        return [
            'number' => $invoice->number ?? $invoice->getKey(),
            'document_type' => __('invoices.document_types.'.$this->enumValue($invoice->document_type)),
            'invoice_date' => $this->formatDate($invoice->invoice_date),
            'due_date' => $this->formatDate($invoice->due_date),
            'seller_name' => (string) $invoice->club_name,
            'seller_organization_number' => (string) $invoice->club_organization_number,
            'recipient_name' => (string) $invoice->recipient_name,
            'recipient_company_name' => $invoice->recipient_company_name,
            'recipient_organization_number' => $invoice->recipient_organization_number,
            'recipient_address' => $invoice->recipient_address,
            'recipient_postal_code' => $invoice->recipient_postal_code,
            'recipient_city' => $invoice->recipient_city,
            'lines' => $lines->map(fn ($line): array => [
                'description' => (string) $line->description,
                'quantity' => number_format((float) $line->quantity, 2, ',', ' '),
                'unit_price' => $this->formatOre($line->gross_unit_price_ore),
                'net_amount' => $this->formatOre($line->net_amount_ore),
                'vat_amount' => $this->formatOre($line->vat_amount_ore),
                'gross_amount' => $this->formatOre($line->gross_amount_ore),
                'vat_treatment' => __('invoices.vat_treatments.'.$this->enumValue($line->vat_treatment)),
            ])->values()->all(),
            'net_total' => $this->formatOre($invoice->net_total_ore),
            'vat_total' => $this->formatOre($invoice->vat_total_ore),
            'gross_total' => $this->formatOre($invoice->gross_total_ore),
        ];
    }

    private function formatOre(int|string|null $ore): string
    {
        return number_format(((int) $ore) / 100, 2, ',', ' ').' kr';
    }

    private function formatDate(mixed $date): string
    {
        return Carbon::parse($date)->locale(app()->getLocale())->translatedFormat('j. F Y');
    }

    private function enumValue(mixed $value): string
    {
        return $value instanceof \BackedEnum ? $value->value : (string) $value;
    }
}
