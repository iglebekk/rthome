@props(['club', 'link' => null])

<x-app.card>
    <x-form :action="$link ? route('clubs.links.update', [$club, $link]) : route('clubs.links.store', $club)" :method="$link ? 'PUT' : 'POST'">
        <x-form.input name="name" :label="__('links.fields.name')" :value="old('name', $link?->name)" required autofocus />
        <x-form.input name="url" type="url" :label="__('links.fields.url')" :value="old('url', $link?->url)" required />
        <x-form.checkbox name="is_pinned" :label="__('links.fields.is_pinned')" value="1" :checked="old('is_pinned', $link?->is_pinned ?? false)" />
        <x-form.actions>
            <x-app.link-button :href="route('clubs.links.index', $club)" variant="ghost">{{ __('app.actions.cancel') }}</x-app.link-button>
            <x-app.button type="submit">{{ __('app.actions.save') }}</x-app.button>
        </x-form.actions>
    </x-form>
</x-app.card>
