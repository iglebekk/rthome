<?php

namespace App\Http\Requests;

use App\Enums\InvoiceCreationStatus;
use App\Models\InvoiceCreation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreInvoiceCreationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $club = $user?->clubs()->findOrFail($this->route('club'));
        $creationId = $this->route('invoiceCreation');

        if ($creationId === null) {
            return $user?->can('create', [InvoiceCreation::class, $club]) ?? false;
        }

        $creation = $club?->invoiceCreations()->findOrFail($creationId);

        if ($creation?->status === InvoiceCreationStatus::Issued && $this->input('intent') === 'issue') {
            return $user?->can('view', $creation) ?? false;
        }

        return $user?->can('update', $creation) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $issuing = $this->input('intent') === 'issue';

        return [
            'intent' => ['required', Rule::in(['draft', 'issue'])],
            'submission_token' => ['required', 'uuid'],
            'invoice_date' => [Rule::requiredIf($issuing), 'nullable', Rule::date()->format('Y-m-d')],
            'due_date' => [Rule::requiredIf($issuing), 'nullable', Rule::date()->format('Y-m-d'), 'after_or_equal:invoice_date'],
            'recipients' => [Rule::requiredIf($issuing), 'array', Rule::when($issuing, ['min:1'])],
            'recipients.*' => ['required', 'integer', 'distinct:strict'],
            'lines' => [Rule::requiredIf($issuing), 'array', Rule::when($issuing, ['min:1'])],
            'lines.*.product_id' => ['required', 'integer', 'distinct:strict'],
            'lines.*.quantity' => ['required', 'regex:/^\d{1,8}(?:\.\d{1,2})?$/', 'not_in:0,0.0,0.00'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [$this->validateClubData(...)];
    }

    protected function validateClubData(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $club = $this->user()->clubs()->findOrFail($this->route('club'));
        $recipientIds = $this->input('recipients', []);
        $productIds = collect($this->input('lines', []))->pluck('product_id')->unique()->values();

        if ($club->members()->whereIn('id', $recipientIds)->count() !== count($recipientIds)) {
            $validator->errors()->add('recipients', __('invoices.validation.recipients_club'));
        }

        $products = $club->products()->whereIn('id', $productIds);

        if ((clone $products)->count() !== $productIds->count()) {
            $validator->errors()->add('lines', __('invoices.validation.products_club'));

            return;
        }

        if ($this->input('intent') !== 'issue') {
            return;
        }

        if (! is_string($club->organization_number) || preg_match('/^\d{9}$/', $club->organization_number) !== 1) {
            $validator->errors()->add('organization_number', __('invoices.validation.organization_number'));
        }

        if (! is_string($club->account_number) || preg_match('/^\d{11}$/', $club->account_number) !== 1) {
            $validator->errors()->add('account_number', __('invoices.validation.account_number'));
        }

        if ((clone $products)->where('is_active', true)->count() !== $productIds->count()) {
            $validator->errors()->add('lines', __('invoices.validation.products_active'));
        }
    }
}
