<?php

namespace App\Http\Requests;

use App\Models\Member;
use App\Models\Position;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePositionRequest extends FormRequest
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

        return $user->can('create', [Position::class, $club]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $clubId = (int) $this->route('club');

        return [
            'member_id' => [
                'nullable',
                'integer',
                Rule::exists(Member::class, 'id')
                    ->where(fn (Builder $query): Builder => $query->where('club_id', $clubId)),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Position::class, 'name')
                    ->where(fn (Builder $query): Builder => $query->where('club_id', $clubId)),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'start_date' => ['required_with:end_date', 'nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ];
    }
}
