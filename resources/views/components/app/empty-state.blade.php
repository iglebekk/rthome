@props(['title', 'description', 'icon' => 'sparkles'])

<div {{ $attributes->class('grid place-items-center gap-4 rounded-lg border border-dashed border-zinc-300 bg-zinc-50 px-6 py-14 text-center dark:border-zinc-700 dark:bg-zinc-900/50') }}>
    <flux:icon :$icon class="size-9 text-emerald-600 dark:text-emerald-400" />
    <div class="grid max-w-md gap-1">
        <flux:heading>{{ $title }}</flux:heading>
        <flux:text>{{ $description }}</flux:text>
    </div>
    @isset($action)
        <div>{{ $action }}</div>
    @endisset
</div>
