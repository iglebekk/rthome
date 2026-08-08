@props(['club', 'members'])

<div class="grid gap-5">
    <x-form :action="route('clubs.members.index', $club)" method="GET" class="sm:grid-cols-[1fr_auto] sm:items-end">
        <x-form.input name="search" :label="__('app.actions.search')" :placeholder="__('members.search_placeholder')" :value="request('search')" />
        <x-app.button type="submit" icon="magnifying-glass">{{ __('app.actions.search') }}</x-app.button>
    </x-form>

    @forelse ($members as $member)
        <x-app.card class="grid gap-4 sm:grid-cols-[1fr_auto] sm:items-center">
            <div class="grid min-w-0 gap-2">
                <div class="flex flex-wrap items-center gap-2"><x-app.heading>{{ $member->name }}</x-app.heading>@if ($member->user_id)<x-app.badge color="emerald">{{ __('profile.verified') }}</x-app.badge>@endif</div>
                <div class="flex flex-wrap gap-x-5 gap-y-1 text-sm text-zinc-600 dark:text-zinc-300">@if ($member->email)<span>{{ $member->email }}</span>@endif @if ($member->phone)<span>{{ $member->phone }}</span>@endif</div>
                <div class="flex flex-wrap gap-2">
                    @forelse ($member->positions as $position)<x-app.badge>{{ $position->name }}</x-app.badge>@empty<x-app.text size="sm">{{ __('members.no_positions') }}</x-app.text>@endforelse
                </div>
            </div>
            <div class="flex items-center gap-2">
                <x-app.icon-button :href="route('clubs.members.edit', [$club, $member])" icon="pencil-square" :label="__('members.actions.edit')" />
                <x-app.dialog
                    :name="'delete-member-'.$member->getKey()"
                    :title="__('members.delete_title', ['name' => $member->name])"
                    :description="__('members.delete_description')"
                    :confirm-label="__('members.actions.delete')"
                    :action="route('clubs.members.destroy', [$club, $member])"
                ><x-slot:trigger><x-app.icon-button icon="trash" :label="__('members.actions.delete')" /></x-slot:trigger></x-app.dialog>
            </div>
        </x-app.card>
    @empty
        <x-story.spotlight-empty-state :title="__('members.empty')" :description="__('members.empty_description')">
            <x-slot:action><x-app.link-button :href="route('clubs.members.create', $club)" icon="plus">{{ __('members.actions.create') }}</x-app.link-button></x-slot:action>
        </x-story.spotlight-empty-state>
    @endforelse

    <x-app.pagination :paginator="$members" />
</div>
