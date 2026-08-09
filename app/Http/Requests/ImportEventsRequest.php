<?php

namespace App\Http\Requests;

use App\Models\Event;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ImportEventsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        $club = $user->clubs()->findOrFail($this->route('club'));

        return $user->can('create', [Event::class, $club]);
    }

    protected function prepareForValidation(): void
    {
        $json = $this->input('json');

        if (! is_string($json) || trim($json) === '') {
            return;
        }

        $decoded = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_object(json_decode($json))) {
            return;
        }

        $this->merge($decoded);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $datePattern = 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d{1,6})?(?:Z|[+-]\d{2}:\d{2})$/';

        return [
            'json' => ['required', 'string', function (string $attribute, mixed $value, Closure $fail): void {
                $decoded = json_decode($value);

                if (json_last_error() !== JSON_ERROR_NONE || ! is_object($decoded)) {
                    $fail(__('clubs.settings.events.validation.invalid_json'));
                }
            }],
            'events' => ['required', 'array', 'max:100', function (string $attribute, mixed $value, Closure $fail): void {
                if (! is_array($value) || ! array_is_list($value)) {
                    $fail(__('clubs.settings.events.validation.events_array'));
                }
            }],
            'events.*' => ['required', 'array'],
            'events.*.name' => ['required', 'string', 'max:255'],
            'events.*.location' => ['nullable', 'string', 'max:255'],
            'events.*.starts_at' => ['required', 'date', $datePattern],
            'events.*.ends_at' => ['required', 'date', 'after:events.*.starts_at', $datePattern],
            'events.*.registration_url' => ['nullable', 'url', 'max:2048'],
            'events.*.short_description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
