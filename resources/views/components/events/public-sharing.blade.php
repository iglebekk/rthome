@props(['club', 'publicEventsUrl'])

<div class="flex items-center gap-1" data-test="public-events-sharing-controls">
    @if ($club->public_events_enabled && $publicEventsUrl)
        <x-app.button
            type="button"
            variant="ghost"
            icon="clipboard-document"
            data-test="public-events-copy"
            data-copy-url="{{ $publicEventsUrl }}"
            data-copied-label="{{ __('events.public_sharing.actions.copied') }}"
        >{{ __('events.public_sharing.actions.copy') }}</x-app.button>
        <x-app.icon-button
            :href="$publicEventsUrl"
            icon="arrow-top-right-on-square"
            :label="__('events.public_sharing.actions.open')"
            target="_blank"
            rel="noopener noreferrer"
            data-test="public-events-open"
        />
        <x-form :action="route('clubs.public-events-link.destroy', $club)" method="DELETE">
            <x-app.icon-button
                type="submit"
                icon="link-slash"
                :label="__('events.public_sharing.actions.disable')"
                data-test="public-events-disable"
            />
        </x-form>
    @else
        <x-form :action="route('clubs.public-events-link.store', $club)">
            <x-app.button type="submit" variant="ghost" icon="link" data-test="public-events-enable">
                {{ __('events.public_sharing.actions.enable') }}
            </x-app.button>
        </x-form>
    @endif
</div>
