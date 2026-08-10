<x-layouts.auth :title="__('clubs.settings.invitations.join_title', ['club' => $invitation->club->name])" :description="__('clubs.settings.invitations.join_description')">
    <x-app.card>
        <x-app.heading size="lg">{{ __('clubs.settings.invitations.join_title', ['club' => $invitation->club->name]) }}</x-app.heading>
        @auth
            <x-app.text>{{ __('clubs.settings.invitations.join_as', ['name' => auth()->user()->name, 'email' => auth()->user()->email]) }}</x-app.text>
            @if (! auth()->user()->hasVerifiedEmail())
                <x-app.alert>{{ __('clubs.settings.invitations.verify_required') }}</x-app.alert>
            @else
                <x-form :action="route('club-invitations.confirm', $token)">
                    <x-app.button type="submit">{{ __('clubs.settings.invitations.join_confirm') }}</x-app.button>
                </x-form>
            @endif
        @else
            <x-auth.identify-form />
        @endauth
    </x-app.card>
</x-layouts.auth>
