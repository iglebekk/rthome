<?php

namespace App\Http\Requests;

use App\Models\Member;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ImportMembersRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        $club = $user->clubs()->findOrFail($this->route('club'));

        return $user->can('create', [Member::class, $club]);
    }

    protected function prepareForValidation(): void
    {
        $json = $this->input('json');

        if (! is_string($json) || trim($json) === '') {
            return;
        }

        $decoded = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_object(json_decode($json)) || ! is_array($decoded)) {
            return;
        }

        if (isset($decoded['members']) && is_array($decoded['members'])) {
            $decoded['members'] = array_map(
                function (mixed $member): mixed {
                    if (! is_array($member)) {
                        return $member;
                    }

                    foreach (['email', 'phone'] as $field) {
                        if (! array_key_exists($field, $member) || ! is_string($member[$field])) {
                            continue;
                        }

                        $value = trim($member[$field]);
                        $member[$field] = $value === ''
                            ? null
                            : ($field === 'email' ? str($value)->lower()->toString() : $value);
                    }

                    return $member;
                },
                $decoded['members'],
            );
        }

        $this->merge($decoded);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'json' => ['required', 'string', function (string $attribute, mixed $value, Closure $fail): void {
                $decoded = json_decode($value);

                if (json_last_error() !== JSON_ERROR_NONE || ! is_object($decoded)) {
                    $fail(__('members.import.validation.invalid_json'));
                }
            }],
            'members' => ['required', 'array', 'max:100', function (string $attribute, mixed $value, Closure $fail): void {
                if (! is_array($value) || ! array_is_list($value)) {
                    $fail(__('members.import.validation.members_array'));
                }
            }],
            'members.*' => ['required', 'array'],
            'members.*.name' => ['required', 'string', 'max:255'],
            'members.*.email' => ['nullable', 'email', 'max:255'],
            'members.*.phone' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $emails = [];

                foreach ($this->input('members', []) as $index => $member) {
                    if (! is_array($member) || ! isset($member['email']) || ! is_string($member['email'])) {
                        continue;
                    }

                    if (isset($emails[$member['email']])) {
                        $validator->errors()->add("members.{$index}.email", __('members.import.validation.duplicate_email'));

                        continue;
                    }

                    $emails[$member['email']] = true;
                }
            },
        ];
    }
}
