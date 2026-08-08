@props(['type' => 'button', 'variant' => 'primary', 'icon' => null])

<flux:button :$type :$variant :$icon {{ $attributes }}>{{ $slot }}</flux:button>
