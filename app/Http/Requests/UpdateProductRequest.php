<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->input('gross_price_ore') === null && $this->filled('gross_price')) {
            $value = trim((string) $this->input('gross_price'));
            if (preg_match('/^\d+(?:\.\d{1,2})?$/', $value) === 1) {
                $parts = array_pad(explode('.', $value, 2), 2, '');
                $whole = $parts[0];
                $fraction = $parts[1];
                $this->merge(['gross_price_ore' => ((int) $whole * 100) + (int) str_pad($fraction, 2, '0')]);
            }
        }
    }

    public function authorize(): bool
    {
        $user = $this->user();
        $club = $user?->clubs()->findOrFail($this->route('club'));
        $product = $club?->products()->findOrFail($this->route('product'));

        return $user?->can('update', $product) ?? false;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'gross_price_ore' => ['required', 'integer', 'min:0'],
            'gross_price' => ['nullable', 'numeric', 'min:0'],
            'vat_treatment' => ['required', Rule::in(['standard', 'reduced_15', 'reduced_12', 'exempt', 'outside'])],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
