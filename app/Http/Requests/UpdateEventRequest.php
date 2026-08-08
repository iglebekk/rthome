<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        $club = $user->clubs()->findOrFail($this->route('club'));
        $event = $club->events()->findOrFail($this->route('event'));

        return $user->can('update', $event);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $datePattern = $this->expectsJson()
            ? 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d{1,6})?(?:Z|[+-]\d{2}:\d{2})$/'
            : 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'starts_at' => ['required_with:ends_at', 'date', $datePattern],
            'ends_at' => ['required_with:starts_at', 'date', 'after:starts_at', $datePattern],
            'registration_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'short_description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'image' => ['sometimes', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:5120', 'prohibits:remove_image'],
            'remove_image' => ['sometimes', 'boolean'],
        ];
    }
}
