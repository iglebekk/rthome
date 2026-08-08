<?php

namespace App\Http\Requests;

use App\Models\Member;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRequest extends FormRequest
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
        $member = $club->members()->findOrFail($this->route('member'));

        return $user->can('update', $member);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $club = $this->user()->clubs()->findOrFail($this->route('club'));
        $member = $club->members()->findOrFail($this->route('member'));

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique(Member::class, 'email')
                    ->where(fn (Builder $query): Builder => $query->where('club_id', $club->getKey()))
                    ->ignore($member),
                Rule::when($member->user_id !== null, Rule::in([$member->email])),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
        ];
    }
}
