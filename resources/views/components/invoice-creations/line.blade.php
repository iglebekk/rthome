@props(['index', 'product', 'quantity' => '1.00'])

<x-app.card
    class="grid gap-4 lg:grid-cols-12 lg:items-end"
    data-invoice-line
    data-product-id="{{ $product->getKey() }}"
    data-product-price-ore="{{ $product->gross_price_ore }}"
    data-product-active="{{ $product->is_active ? 'true' : 'false' }}"
>
    <x-form.hidden name="lines[{{ $index }}][product_id]" :value="$product->getKey()" data-product-id-input />
    <div class="grid gap-1 lg:col-span-3">
        <x-app.text size="sm">{{ __('invoices.fields.product') }}</x-app.text>
        <x-app.heading size="sm">{{ $product->name }}</x-app.heading>
    </div>
    <div class="grid gap-1 lg:col-span-3">
        <x-app.text size="sm">{{ __('invoices.fields.description') }}</x-app.text>
        <x-app.text>{{ $product->description ?: $product->name }}</x-app.text>
    </div>
    <div class="grid gap-1 lg:col-span-2">
        <x-app.text size="sm">{{ __('invoices.fields.unit_price') }}</x-app.text>
        <x-app.text>{{ number_format($product->gross_price_ore / 100, 2, '.', ' ') }}</x-app.text>
    </div>
    <div class="lg:col-span-2">
        <x-form.input
            name="lines[{{ $index }}][quantity]"
            type="number"
            step="0.01"
            min="0.01"
            :label="__('invoices.fields.quantity')"
            :value="$quantity"
            required
            data-line-quantity
        />
    </div>
    <div class="grid gap-1 lg:col-span-2">
        <x-app.text size="sm">{{ __('invoices.fields.vat_rate') }}</x-app.text>
        <x-app.text>{{ __('invoices.vat_treatments.'.$product->vat_treatment->value) }}</x-app.text>
    </div>
    @unless ($product->is_active)
        <x-app.alert class="lg:col-span-12" variant="warning">{{ __('invoices.inactive_product_warning') }}</x-app.alert>
    @endunless
    <div class="flex items-center justify-between gap-3 lg:col-span-12">
        <x-app.text>
            {{ __('invoices.fields.line_total') }}:
            <strong data-line-total>{{ __('invoices.zero_total') }}</strong>
        </x-app.text>
        <x-app.button type="button" variant="ghost" icon="trash" data-remove-invoice-line>
            {{ __('invoices.actions.remove_line') }}
        </x-app.button>
    </div>
</x-app.card>
