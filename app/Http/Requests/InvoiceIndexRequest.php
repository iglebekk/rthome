<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvoiceIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->clubs()->whereKey($this->route('club'))->exists() ?? false;
    }

    public function rules(): array
    {
        return [
            'settlement' => ['sometimes', 'string', Rule::in(['all', 'outstanding', 'settled'])],
            'sort' => ['sometimes', 'string', Rule::in(['number', 'due_date', 'overdue'])],
        ];
    }

    public function settlement(): string
    {
        return $this->validated('settlement', 'all');
    }

    public function sort(): string
    {
        return $this->validated('sort', 'number');
    }
}
