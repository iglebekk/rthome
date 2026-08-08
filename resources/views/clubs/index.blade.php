<x-layouts.app :title="__('clubs.index.title')">
    <x-app.page-header :title="__('clubs.index.title')" :description="__('clubs.index.description')">
        <x-slot:actions>
            <x-app.link-button :href="route('clubs.create')" icon="plus">
                {{ __('clubs.actions.create') }}
            </x-app.link-button>
        </x-slot:actions>
    </x-app.page-header>

    @if ($clubs->isEmpty())
        <x-app.empty-state
            :title="__('clubs.index.empty_title')"
            :description="__('clubs.index.empty_description')"
            icon="building-office-2"
        >
            <x-slot:action>
                <x-app.link-button :href="route('clubs.create')" icon="plus">
                    {{ __('clubs.actions.create') }}
                </x-app.link-button>
            </x-slot:action>
        </x-app.empty-state>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($clubs as $club)
                <x-app.card class="grid gap-3">
                    <flux:icon name="building-office-2" class="size-6 text-emerald-600 dark:text-emerald-400" />
                    <x-app.text-link :href="route('clubs.dashboard', $club)" class="text-base">
                        {{ $club->name }}
                    </x-app.text-link>
                </x-app.card>
            @endforeach
        </div>
    @endif
</x-layouts.app>
