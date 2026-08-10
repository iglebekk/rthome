<?php

namespace App\Http\Requests;

use App\Models\ClubInvitation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreClubInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        $club = $user->clubs()->findOrFail($this->route('club'));

        return $user->can('create', [ClubInvitation::class, $club]);
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'days' => ['required', 'integer', 'in:1,7,30,90'],
        ];
    }
}
