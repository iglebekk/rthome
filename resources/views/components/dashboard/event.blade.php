@props(['event', 'club'])

<a href="{{ route('clubs.events.show', [$club, $event]) }}" class="block">
    <x-app.card class="flex items-start gap-4 p-4 transition hover:border-emerald-300 dark:hover:border-emerald-700">
        <div class="grid gap-1">
            <flux:heading>{{ $event->name }}</flux:heading>
            <flux:text size="sm">{{ $event->starts_at->translatedFormat('M j, Y · H:i') }}</flux:text>
            @if ($event->location)
                <flux:text size="sm">{{ $event->location }}</flux:text>
            @endif
        </div>
    </x-app.card>
</a>
