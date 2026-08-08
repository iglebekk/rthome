@props(['event', 'club'])

<a href="{{ route('clubs.events.show', [$club, $event]) }}" class="group block">
    <article class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm transition duration-300 motion-safe:group-hover:-translate-y-1 dark:border-zinc-800 dark:bg-zinc-900">
        @if ($event->image_path)
            <img src="{{ $event->imageUrl() }}" alt="" class="aspect-[16/9] w-full object-cover transition duration-500 motion-safe:group-hover:scale-[1.03]">
        @else
            <div class="grid aspect-[16/9] place-items-center bg-gradient-to-br from-emerald-100 to-stone-100 dark:from-emerald-950 dark:to-zinc-900"><flux:icon name="calendar-days" class="size-10 text-emerald-700 dark:text-emerald-300" /></div>
        @endif
        <div class="grid gap-3 p-5">
            <div class="grid gap-1"><flux:heading>{{ $event->name }}</flux:heading><flux:text size="sm">{{ $event->starts_at->translatedFormat('M j, Y · H:i') }}</flux:text></div>
            @if ($event->location)<flux:text>{{ $event->location }}</flux:text>@endif
            @if ($event->short_description)<p class="text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $event->short_description }}</p>@endif
        </div>
    </article>
</a>
