@props(['title' => null, 'description' => null])

<section {{ $attributes->class('grid content-start gap-4') }}>
    @if ($title || $description)
        <header class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="grid min-w-0 gap-1">
                @if ($title)
                    <flux:heading size="lg">{{ $title }}</flux:heading>
                @endif
                @if ($description)
                    <flux:text>{{ $description }}</flux:text>
                @endif
            </div>
            @isset($actions)
                <div class="shrink-0 sm:max-w-[50%]">{{ $actions }}</div>
            @endisset
        </header>
    @endif
    {{ $slot }}
</section>
