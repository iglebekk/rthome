@props(['icon' => null])

<flux:menu.item :$icon {{ $attributes }}>
    {{ $slot }}
</flux:menu.item>
