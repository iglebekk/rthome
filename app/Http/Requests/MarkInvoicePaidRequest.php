<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class MarkInvoicePaidRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $club = $user?->clubs()->findOrFail($this->route('club'));
        $invoice = $club?->invoices()->findOrFail($this->route('invoice'));

        return $user?->can('markPaid', $invoice) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'paid_amount' => ['required', 'numeric', 'min:0.01'],
            'paid_date' => ['required', 'date'],
        ];
    }

    public function paidAmountOre(): int
    {
        return (int) round((float) str_replace(',', '.', (string) $this->validated('paid_amount')) * 100);
    }

    public function paidDate(): Carbon
    {
        return Carbon::parse($this->validated('paid_date'));
    }
}
