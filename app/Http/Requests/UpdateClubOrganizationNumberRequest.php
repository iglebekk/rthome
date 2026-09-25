<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateClubOrganizationNumberRequest extends FormRequest
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
            'organization_number' => ['required', 'digits:9'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'organization_number' => preg_replace(
                '/\\s+/',
                '',
                $this->string('organization_number')->toString(),
            ),
        ]);
    }
}
