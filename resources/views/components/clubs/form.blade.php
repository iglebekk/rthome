@props(['club' => null])

<x-app.card>
    <x-form :action="$club ? route('clubs.update', $club) : route('clubs.store')" :method="$club ? 'PUT' : 'POST'">
        <x-form.input name="name" :label="__('clubs.settings.name')" :value="old('name', $club?->name)" required autofocus />
        <x-form.actions>
            @if ($club)<x-app.link-button :href="route('clubs.dashboard', $club)" variant="ghost">{{ __('app.actions.cancel') }}</x-app.link-button>@endif
            <x-app.button type="submit">{{ $club ? __('app.actions.save') : __('clubs.create.submit') }}</x-app.button>
        </x-form.actions>
    </x-form>
</x-app.card>
