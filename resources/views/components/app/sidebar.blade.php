@props(['club', 'availableClubs', 'backToClub' => false])

<div class="flex min-h-screen flex-col gap-6 px-4 py-5">
    <a href="{{ route('home') }}" class="flex items-center gap-3 px-2 text-zinc-950 dark:text-white">
        <span class="grid size-9 place-items-center rounded-lg bg-emerald-600 text-sm font-bold text-white">{{ __('app.brand_mark') }}</span>
        <span class="font-semibold tracking-tight">{{ config('app.name') }}</span>
    </a>

    @if ($backToClub && $availableClubs->isNotEmpty())
        <x-app.link-button :href="route('home')" variant="ghost" icon="arrow-left" class="w-full justify-start">
            {{ __('app.navigation.back_to_club') }}
        </x-app.link-button>
    @endif

    @if ($club)
        <nav class="grid gap-1" aria-label="{{ __('app.navigation.primary') }}">
            <a href="{{ route('clubs.dashboard', $club) }}" class="app-nav-link"><flux:icon name="home" class="size-5" />{{ __('app.navigation.dashboard') }}</a>
            <a href="{{ route('clubs.members.index', $club) }}" class="app-nav-link"><flux:icon name="users" class="size-5" />{{ __('app.navigation.members') }}</a>
            <a href="{{ route('clubs.positions.index', $club) }}" class="app-nav-link"><flux:icon name="briefcase" class="size-5" />{{ __('app.navigation.positions') }}</a>
            <a href="{{ route('clubs.events.index', $club) }}" class="app-nav-link"><flux:icon name="calendar-days" class="size-5" />{{ __('app.navigation.events') }}</a>
            <a href="{{ route('clubs.links.index', $club) }}" class="app-nav-link"><flux:icon name="link" class="size-5" />{{ __('app.navigation.links') }}</a>
            <a href="{{ route('clubs.edit', $club) }}" class="app-nav-link"><flux:icon name="cog-6-tooth" class="size-5" />{{ __('app.navigation.settings') }}</a>
        </nav>
    @endif

    <div class="mt-auto grid gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-800">
        <a href="{{ route('profile.show') }}" class="app-nav-link"><flux:icon name="user-circle" class="size-5" />{{ __('app.navigation.profile') }}</a>
        <a href="{{ route('clubs.index') }}" class="app-nav-link"><flux:icon name="building-office-2" class="size-5" />{{ __('app.navigation.clubs') }}</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-app.button type="submit" variant="ghost" icon="arrow-left-start-on-rectangle" class="w-full justify-start">
                {{ __('auth.logout') }}
            </x-app.button>
        </form>
    </div>
</div>
