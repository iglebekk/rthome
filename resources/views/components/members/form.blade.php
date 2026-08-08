@props(['club', 'member' => null])

<x-app.card>
    <x-form :action="$member ? route('clubs.members.update', [$club, $member]) : route('clubs.members.store', $club)" :method="$member ? 'PUT' : 'POST'">
        <x-form.input name="name" :label="__('members.fields.name')" :value="old('name', $member?->name)" required autofocus />
        <x-form.input name="email" type="email" :label="__('members.fields.email')" :value="old('email', $member?->email)" :readonly="$member?->user_id !== null" />
        @if ($member?->user_id !== null)<x-app.text size="sm">{{ __('members.linked_email') }}</x-app.text>@endif
        <x-form.input name="phone" type="tel" :label="__('members.fields.phone')" :value="old('phone', $member?->phone)" autocomplete="tel" />
        <x-form.actions>
            <x-app.link-button :href="route('clubs.members.index', $club)" variant="ghost">{{ __('app.actions.cancel') }}</x-app.link-button>
            <x-app.button type="submit">{{ __('app.actions.save') }}</x-app.button>
        </x-form.actions>
    </x-form>
</x-app.card>
