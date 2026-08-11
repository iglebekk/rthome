@props(['club', 'links'])

<div class="grid content-start gap-4">
    @forelse ($links as $link)
        <x-links.card :$link :$club />
    @empty
        <x-story.spotlight-empty-state :title="__('links.empty')" :description="__('links.empty_description')">
            <x-slot:action><x-app.link-button :href="route('clubs.links.create', $club)" icon="plus">{{ __('links.actions.create') }}</x-app.link-button></x-slot:action>
        </x-story.spotlight-empty-state>
    @endforelse
    <x-app.pagination :paginator="$links" />
</div>
