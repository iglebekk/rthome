@props(['title' => null, 'description' => null])

<section {{ $attributes->class('grid gap-4') }}>
    @if ($title || $description)
        <header class="grid gap-1">
            @if ($title)
                <flux:heading size="lg">{{ $title }}</flux:heading>
            @endif
            @if ($description)
                <flux:text>{{ $description }}</flux:text>
            @endif
        </header>
    @endif
    {{ $slot }}
</section>
