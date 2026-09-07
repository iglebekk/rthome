<?php

namespace App\Http\Requests;

use App\Models\Member;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LookupBrregEntityRequest extends FormRequest
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

        return $user->can('create', [Member::class, $club]);
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
                (string) $this->route('organizationNumber'),
            ),
        ]);
    }
}
