@props(['href', 'variant' => 'primary', 'icon' => null])

<flux:button :$href :$variant :$icon {{ $attributes }}>{{ $slot }}</flux:button>
