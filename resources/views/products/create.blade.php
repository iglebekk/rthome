<x-layouts.app :title="__('products.create_title')" :$club>
    <x-app.page-header :title="__('products.create_title')" :description="__('products.create_description')" :eyebrow="$club->name" />
    <x-products.form :$club />
</x-layouts.app>
