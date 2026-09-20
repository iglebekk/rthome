@props(['club', 'product' => null])

<x-app.card>
    <x-form :action="$product ? route('clubs.products.update', [$club, $product]) : route('clubs.products.store', $club)" :method="$product ? 'PUT' : 'POST'">
        <x-form.input name="name" :label="__('products.fields.name')" :value="old('name', $product?->name)" required autofocus />
        <x-form.textarea name="description" :label="__('products.fields.description')" :value="old('description', $product?->description)" rows="3" />
        <div class="grid gap-5 sm:grid-cols-2">
            <x-form.input name="gross_price" type="number" step="0.01" min="0" inputmode="decimal" :label="__('products.fields.price')" :value="old('gross_price', $product?->gross_price_ore !== null ? $product->gross_price_ore / 100 : null)" required />
            <x-form.select name="vat_treatment" :label="__('products.fields.vat_rate')" :options="__('invoices.vat_treatments')" :value="old('vat_treatment', $product?->vat_treatment?->value ?? 'standard')" required />
        </div>
        <x-form.checkbox name="is_active" :label="__('products.fields.active')" :checked="old('is_active', $product?->is_active ?? true)" value="1" />
        <x-form.actions>
            <x-app.link-button :href="route('clubs.products.index', $club)" variant="ghost">{{ __('app.actions.cancel') }}</x-app.link-button>
            <x-app.button type="submit">{{ $product ? __('app.actions.save') : __('products.actions.create') }}</x-app.button>
        </x-form.actions>
    </x-form>
</x-app.card>
