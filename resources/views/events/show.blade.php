<x-layouts.app :title="__('events.view_title')" :$club>
    <x-app.page-header :title="$event->name" :description="__('events.view_description')" :eyebrow="$club->name">
        <x-slot:actions>
            <x-app.link-button :href="route('clubs.events.index', $club)" variant="ghost">{{ __('app.actions.back') }}</x-app.link-button>
            <x-app.link-button :href="route('clubs.events.edit', [$club, $event])" icon="pencil-square">{{ __('events.actions.edit') }}</x-app.link-button>
        </x-slot:actions>
    </x-app.page-header>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(16rem,1fr)]">
        <x-app.card class="overflow-hidden">
            @if ($event->image_path)
                <img src="{{ $event->imageUrl() }}" alt="" class="aspect-[16/9] w-full object-cover">
            @else
                <div class="grid aspect-[16/9] place-items-center bg-gradient-to-br from-emerald-100 to-stone-100 dark:from-emerald-950 dark:to-zinc-900">
                    <flux:icon name="calendar-days" class="size-12 text-emerald-700 dark:text-emerald-300" />
                </div>
            @endif
            <div class="grid gap-4 p-6">
                @if ($event->short_description)
                    <x-app.text>{{ $event->short_description }}</x-app.text>
                @endif
                @if ($event->registration_url)
                    <x-app.text-link :href="$event->registration_url" target="_blank" rel="noopener noreferrer">{{ __('events.actions.register') }}</x-app.text-link>
                @endif
            </div>
        </x-app.card>

        <x-app.card class="grid content-start gap-5 p-6">
            <div class="grid gap-1">
                <x-app.text size="sm">{{ __('events.fields.starts_at') }}</x-app.text>
                <x-app.heading size="lg">{{ $event->starts_at->translatedFormat('M j, Y · H:i') }}</x-app.heading>
            </div>
            <div class="grid gap-1">
                <x-app.text size="sm">{{ __('events.fields.ends_at') }}</x-app.text>
                <x-app.heading size="lg">{{ $event->ends_at->translatedFormat('M j, Y · H:i') }}</x-app.heading>
            </div>
            @if ($event->location)
                <div class="grid gap-1">
                    <x-app.text size="sm">{{ __('events.fields.location') }}</x-app.text>
                    <x-app.text>{{ $event->location }}</x-app.text>
                </div>
            @endif
        </x-app.card>
    </div>
</x-layouts.app>
