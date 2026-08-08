@props(['label', 'value', 'description' => null, 'icon' => 'chart-bar'])

<x-app.card class="grid gap-3">
    <div class="flex items-center justify-between gap-4">
        <flux:text>{{ $label }}</flux:text>
        <flux:icon :$icon class="size-5 text-emerald-600 dark:text-emerald-400" />
    </div>
    <p class="text-3xl font-semibold tracking-tight">{{ $value }}</p>
    @if ($description)<flux:text size="sm">{{ $description }}</flux:text>@endif
</x-app.card>
