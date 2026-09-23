<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClubRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        $club = $user->clubs()->findOrFail($this->route('club'));

        return $user->can('update', $club);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'organization_number' => ['nullable', 'digits:9'],
            'account_number' => ['nullable', 'digits:11'],
            'locale' => ['sometimes', Rule::in(['nb', 'en'])],
        ];
    }
}
