@props(['label', 'icon', 'type' => 'button', 'variant' => 'ghost'])

<flux:tooltip :content="$label">
    <flux:button :$type :$variant :$icon :aria-label="$label" square {{ $attributes }} />
</flux:tooltip>
