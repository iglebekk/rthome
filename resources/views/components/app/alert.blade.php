@props(['variant' => 'success', 'title' => null])

<flux:callout :variant="$variant === 'danger' ? 'danger' : null" icon="{{ in_array($variant, ['danger', 'warning']) ? 'exclamation-triangle' : 'check-circle' }}" {{ $attributes }}>
    @if ($title)
        <flux:callout.heading>{{ $title }}</flux:callout.heading>
    @endif
    <flux:callout.text>{{ $slot }}</flux:callout.text>
</flux:callout>
