@props([
    'club',
    'members',
    'products',
    'submissionToken',
    'invoiceDate',
    'dueDate',
    'selectedMembers',
    'selectedLines',
    'creation' => null,
])

<x-form
    :action="$creation ? route('clubs.invoice-creations.update', [$club, $creation]) : route('clubs.invoice-creations.store', $club)"
    :method="$creation ? 'PUT' : 'POST'"
    data-invoice-creation
    data-locale="{{ app()->getLocale() }}"
    data-single-label="{{ __('invoices.actions.issue_one') }}"
    data-multiple-label="{{ __('invoices.actions.issue_many', ['count' => '__COUNT__']) }}"
    data-duplicate-recipient="{{ __('invoices.validation.duplicate_recipient') }}"
    data-duplicate-product="{{ __('invoices.validation.duplicate_product') }}"
>
    <x-form.hidden name="submission_token" :value="$submissionToken" />

    <x-app.card>
        <x-app.section :title="__('invoices.sections.details')" :description="__('invoices.sections.details_help')">
            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.input name="invoice_date" type="date" :label="__('invoices.fields.invoice_date')" :value="$invoiceDate" />
                <x-form.input name="due_date" type="date" :label="__('invoices.fields.due_date')" :value="$dueDate" />
            </div>
            <x-form.error name="organization_number" />
            <x-form.error name="invoice_name" />
            <x-form.error name="account_number" />
        </x-app.section>
    </x-app.card>

    <x-app.card>
        <x-app.section :title="__('invoices.sections.recipients')" :description="__('invoices.sections.recipients_help')">
            <x-slot:actions>
                <x-app.modal name="invoice-recipients" :title="__('invoices.modals.recipients_title')" :trigger-label="__('invoices.actions.add_recipient')">
                    <x-form.input name="recipient_picker_search" type="search" :label="__('invoices.fields.search_member')" :placeholder="__('invoices.placeholders.search_member')" data-picker-search="recipient" />
                    <div class="grid max-h-96 gap-3 overflow-y-auto" data-picker-options="recipient">
                        @foreach ($members as $member)
                            <div data-picker-option-row="recipient">
                                <x-form.checkbox
                                    name="recipient_picker[]"
                                    :label="$member->name"
                                    :value="$member->getKey()"
                                    data-picker-option="recipient"
                                    data-picker-id="{{ $member->getKey() }}"
                                />
                            </div>
                        @endforeach
                    </div>
                    <x-slot:actions>
                        <x-app.button type="button" data-add-selected-recipients>{{ __('invoices.actions.add_selected') }}</x-app.button>
                    </x-slot:actions>
                </x-app.modal>
            </x-slot:actions>
            <div class="grid gap-4" data-invoice-recipients>
                @foreach ($selectedMembers as $member)
                    <x-invoice-creations.recipient :$member />
                @endforeach
            </div>
            <x-form.error name="recipients" />
            <x-app.text class="hidden text-red-600 dark:text-red-400" data-recipient-error />
        </x-app.section>
    </x-app.card>

    <x-app.card>
        <x-app.section :title="__('invoices.sections.lines')" :description="__('invoices.sections.lines_help')">
            <x-slot:actions>
                <x-app.modal name="invoice-products" :title="__('invoices.modals.products_title')" :trigger-label="__('invoices.actions.add_line')">
                    <x-form.input name="product_picker_search" type="search" :label="__('invoices.fields.search_product')" :placeholder="__('invoices.placeholders.search_product')" data-picker-search="product" />
                    <div class="grid max-h-96 gap-3 overflow-y-auto" data-picker-options="product">
                        @foreach ($products as $product)
                            <div class="grid gap-1" data-picker-option-row="product">
                                <x-form.checkbox
                                    name="product_picker[]"
                                    :label="$product->name"
                                    :value="$product->getKey()"
                                    :disabled="! $product->is_active"
                                    data-picker-option="product"
                                    data-picker-id="{{ $product->getKey() }}"
                                    data-picker-unavailable="{{ $product->is_active ? 'false' : 'true' }}"
                                />
                                @unless ($product->is_active)
                                    <x-app.text size="sm">{{ __('invoices.inactive_product_short') }}</x-app.text>
                                @endunless
                            </div>
                        @endforeach
                    </div>
                    <x-slot:actions>
                        <x-app.button type="button" data-add-selected-products>{{ __('invoices.actions.add_selected') }}</x-app.button>
                    </x-slot:actions>
                </x-app.modal>
            </x-slot:actions>
            <div class="grid gap-4" data-invoice-lines>
                @foreach ($selectedLines as $index => $line)
                    <x-invoice-creations.line :$index :product="$line['product']" :quantity="$line['quantity']" />
                @endforeach
            </div>
            <x-form.error name="lines" />
            <x-app.text class="hidden text-red-600 dark:text-red-400" data-product-error />
            <div class="flex items-center justify-between gap-4 border-t border-zinc-200 pt-5 dark:border-zinc-700">
                <x-app.heading size="lg">{{ __('invoices.fields.total_including_vat') }}</x-app.heading>
                <x-app.heading size="lg" data-invoice-total>{{ __('invoices.zero_total') }}</x-app.heading>
            </div>
        </x-app.section>
    </x-app.card>

    @foreach ($members as $member)
        <template data-recipient-template="{{ $member->getKey() }}">
            <x-invoice-creations.recipient :$member />
        </template>
    @endforeach
    @foreach ($products as $product)
        <template data-product-template="{{ $product->getKey() }}">
            <x-invoice-creations.line index="__INDEX__" :$product quantity="1.00" />
        </template>
    @endforeach

    <x-form.actions>
        <x-app.link-button :href="route('clubs.invoices.index', $club)" variant="ghost">
            {{ __('app.actions.cancel') }}
        </x-app.link-button>
        <x-app.button type="submit" name="intent" value="draft" variant="filled" icon="document-text">
            {{ __('invoices.actions.save_draft') }}
        </x-app.button>
        <x-app.button type="submit" name="intent" value="issue" icon="document-check" data-create-invoices>
            {{ __('invoices.actions.issue_one') }}
        </x-app.button>
    </x-form.actions>
</x-form>
