<x-layouts.app :title="__('clubs.settings.invitations.title')" :$club>
    <x-app.page-header :title="__('clubs.settings.invitations.title')" :description="__('clubs.settings.invitations.description')" :eyebrow="$club->name" />

    @if (session('invitation_url'))
        <x-app.alert>{{ session('invitation_url') }}</x-app.alert>
    @endif

    <x-app.card>
        <x-app.heading size="lg">{{ __('clubs.settings.invitations.create') }}</x-app.heading>
        <x-form :action="route('clubs.settings.invitations.store', $club)">
            <x-form.input name="name" :label="__('clubs.settings.invitations.name')" :value="old('name')" />
            <x-form.select name="days" :label="__('clubs.settings.invitations.duration')" :options="__('clubs.settings.invitations.durations')" :value="old('days', 7)" required />
            <x-form.actions><x-app.button type="submit">{{ __('clubs.settings.invitations.create') }}</x-app.button></x-form.actions>
        </x-form>
    </x-app.card>

    <x-app.section>
        @forelse ($invitations as $invitation)
            <x-app.card class="gap-3">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="grid gap-1">
                        <x-app.heading>{{ $invitation->name ?: __('clubs.settings.invitations.default_name') }}</x-app.heading>
                        <x-app.text>{{ $invitation->isUsable() ? __('clubs.settings.invitations.status_active', ['date' => $invitation->expires_at->toDayDateTimeString()]) : ($invitation->revoked_at ? __('clubs.settings.invitations.status_revoked', ['date' => $invitation->revoked_at->toDayDateTimeString()]) : __('clubs.settings.invitations.status_expired', ['date' => $invitation->expires_at->toDayDateTimeString()])) }}</x-app.text>
                    </div>
                    @if ($invitation->isUsable())
                        <x-form :action="route('club-invitations.destroy', $invitation)" method="DELETE">
                            <x-app.button type="submit" variant="danger">{{ __('clubs.settings.invitations.deactivate') }}</x-app.button>
                        </x-form>
                    @endif
                </div>
                @if ($invitation->isUsable())
                    <div class="flex flex-wrap items-center gap-3">
                        <x-app.button
                            type="button"
                            data-copy-url="{{ route('club-invitations.show', ['token' => $invitation->token_hash]) }}"
                            data-copied-label="{{ __('clubs.settings.invitations.copied') }}"
                        >{{ __('clubs.settings.invitations.copy') }}</x-app.button>
                    </div>
                @endif
            </x-app.card>
        @empty
            <x-app.empty-state :title="__('clubs.settings.invitations.empty')" :description="__('clubs.settings.invitations.empty_description')" />
        @endforelse
    </x-app.section>
</x-layouts.app>
