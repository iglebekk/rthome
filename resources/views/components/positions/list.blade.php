@props(['club', 'positions'])

<div class="grid gap-4">
    @forelse ($positions as $position)
        <div class="grid grid-cols-[auto_1fr] items-center gap-3">
            <span class="text-sm font-semibold tabular-nums text-zinc-400">{{ $position->sort_order }}</span>
            <x-app.position-card :$position :$club />
        </div>
    @empty
        <x-story.spotlight-empty-state :title="__('positions.empty')" :description="__('positions.empty_description')">
            <x-slot:action><x-app.link-button :href="route('clubs.positions.create', $club)" icon="plus">{{ __('positions.actions.create') }}</x-app.link-button></x-slot:action>
        </x-story.spotlight-empty-state>
    @endforelse
</div>
