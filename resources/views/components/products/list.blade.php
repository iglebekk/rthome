@props(['club', 'products'])

<div class="grid gap-5">
    @forelse ($products as $product)
        <x-app.card class="grid gap-4 sm:grid-cols-[1fr_auto] sm:items-center">
            <div class="grid gap-2">
                <div class="flex flex-wrap items-center gap-2">
                    <x-app.heading>{{ $product->name }}</x-app.heading>
                    <x-app.badge color="{{ $product->is_active ? 'emerald' : 'zinc' }}">{{ $product->is_active ? __('status.active') : __('status.inactive') }}</x-app.badge>
                </div>
                @if ($product->description)<x-app.text size="sm">{{ $product->description }}</x-app.text>@endif
                <x-app.text size="sm">{{ number_format($product->gross_price_ore / 100, 2, '.', ' ') }} · {{ __('invoices.vat_treatments.'.$product->vat_treatment->value) }}</x-app.text>
            </div>
            <div class="flex items-center gap-2">
                <x-app.icon-button :href="route('clubs.products.edit', [$club, $product])" icon="pencil-square" :label="__('products.actions.edit')" />
                @if ($product->draft_lines_count > 0)
                    <x-app.badge color="amber">{{ __('products.used_by_draft') }}</x-app.badge>
                @else
                    <x-app.dialog :name="'delete-product-'.$product->getKey()" :title="__('products.delete_title', ['name' => $product->name])" :description="__('products.delete_description')" :confirm-label="__('products.actions.delete')" :action="route('clubs.products.destroy', [$club, $product])">
                        <x-slot:trigger><x-app.icon-button icon="trash" :label="__('products.actions.delete')" /></x-slot:trigger>
                    </x-app.dialog>
                @endif
            </div>
        </x-app.card>
    @empty
        <x-story.spotlight-empty-state :title="__('products.empty')" :description="__('products.empty_description')">
            <x-slot:action><x-app.link-button :href="route('clubs.products.create', $club)" icon="plus">{{ __('products.actions.create') }}</x-app.link-button></x-slot:action>
        </x-story.spotlight-empty-state>
    @endforelse
    @if (method_exists($products, 'links'))<x-app.pagination :paginator="$products" />@endif
</div>
