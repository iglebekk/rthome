<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreditInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $club = $user?->clubs()->findOrFail($this->route('club'));
        $invoice = $club?->invoices()->findOrFail($this->route('invoice'));

        return $user?->can('credit', $invoice) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [];
    }
}
