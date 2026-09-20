<x-layouts.app :title="__('products.edit_title')" :$club>
    <x-app.page-header :title="__('products.edit_title')" :description="__('products.edit_description')" :eyebrow="$club->name">
        <x-slot:actions>
            <x-app.dialog :name="'delete-product-'.$product->getKey()" :title="__('products.delete_title', ['name' => $product->name])" :description="__('products.delete_description')" :confirm-label="__('products.actions.delete')" :action="route('clubs.products.destroy', [$club, $product])">
                <x-slot:trigger><x-app.button variant="danger" icon="trash">{{ __('products.actions.delete') }}</x-app.button></x-slot:trigger>
            </x-app.dialog>
        </x-slot:actions>
    </x-app.page-header>
    <x-products.form :$club :$product />
</x-layouts.app>
