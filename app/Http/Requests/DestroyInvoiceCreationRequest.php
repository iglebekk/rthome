<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DestroyInvoiceCreationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $club = $user?->clubs()->findOrFail($this->route('club'));
        $creation = $club?->invoiceCreations()->findOrFail($this->route('invoiceCreation'));

        return $user?->can('delete', $creation) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [];
    }
}
