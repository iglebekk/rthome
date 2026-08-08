@props(['title', 'description' => null, 'eyebrow' => null])

<header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div class="grid max-w-3xl gap-2">
        @if ($eyebrow)
            <p class="text-xs font-semibold tracking-[0.18em] text-emerald-700 uppercase dark:text-emerald-400">{{ $eyebrow }}</p>
        @endif
        <flux:heading size="xl" level="1">{{ $title }}</flux:heading>
        @if ($description)
            <flux:text class="text-base">{{ $description }}</flux:text>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap gap-2">{{ $actions }}</div>
    @endisset
</header>
