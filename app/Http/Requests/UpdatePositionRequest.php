<?php

namespace App\Http\Requests;

use App\Models\Member;
use App\Models\Position;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdatePositionRequest extends FormRequest
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
        $position = $club->positions()->findOrFail($this->route('position'));

        return $user->can('update', $position);
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
                'sometimes',
                'required',
                'integer',
                Rule::exists(Member::class, 'id')
                    ->where(fn (Builder $query): Builder => $query->where('club_id', $clubId)),
            ],
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique(Position::class, 'name')
                    ->where(fn (Builder $query): Builder => $query->where('club_id', $clubId))
                    ->ignore($this->route('position')),
            ],
            'sort_order' => ['sometimes', 'required', 'integer', 'min:0'],
            'start_date' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'end_date' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
        ];
    }

    /**
     * Get the after validation callables for the request.
     *
     * @return array<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['start_date', 'end_date'])) {
                    return;
                }

                $club = $this->user()->clubs()->findOrFail($this->route('club'));
                $position = $club->positions()->findOrFail($this->route('position'));
                $startDate = $this->has('start_date')
                    ? $this->input('start_date')
                    : $position->start_date?->toDateString();
                $endDate = $this->has('end_date')
                    ? $this->input('end_date')
                    : $position->end_date?->toDateString();

                if ($endDate !== null && $startDate === null) {
                    $validator->errors()->add('start_date', __('validation.required', [
                        'attribute' => __('positions.fields.start_date'),
                    ]));

                    return;
                }

                if ($endDate !== null && $startDate !== null && $endDate < $startDate) {
                    $validator->errors()->add('end_date', __('validation.after_or_equal', [
                        'attribute' => __('positions.fields.end_date'),
                        'date' => __('positions.fields.start_date'),
                    ]));
                }
            },
        ];
    }
}
