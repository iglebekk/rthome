<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnmarkInvoicePaidRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
