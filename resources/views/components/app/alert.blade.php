@props(['variant' => 'success', 'title' => null])

<flux:callout :variant="$variant === 'danger' ? 'danger' : null" icon="{{ $variant === 'danger' ? 'exclamation-triangle' : 'check-circle' }}" {{ $attributes }}>
    @if ($title)
        <flux:callout.heading>{{ $title }}</flux:callout.heading>
    @endif
    <flux:callout.text>{{ $slot }}</flux:callout.text>
</flux:callout>
