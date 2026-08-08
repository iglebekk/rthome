@props(['link', 'club'])

<a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="block">
    <x-app.card class="grid gap-3 p-5 transition hover:border-emerald-300 dark:hover:border-emerald-700">
        <div class="flex items-start justify-between gap-4">
            <div class="grid min-w-0 gap-1">
                <flux:heading>{{ $link->name }}</flux:heading>
                <flux:text size="sm" class="truncate">{{ $link->url }}</flux:text>
            </div>
            @if ($link->is_pinned)<x-app.badge>{{ __('links.pinned') }}</x-app.badge>@endif
        </div>
    </x-app.card>
</a>
