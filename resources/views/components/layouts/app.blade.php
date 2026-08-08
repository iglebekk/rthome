@props(['title', 'club' => null, 'backToClub' => false])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light dark">
        <title>{{ $title }} · {{ config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <x-app.flux-styles />
    </head>
    <body class="min-h-screen bg-stone-50 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100 lg:h-screen lg:overflow-hidden" x-data="{ navigationOpen: false }">
        <div class="min-h-screen lg:grid lg:h-screen lg:grid-cols-[17rem_1fr]">
            <aside class="hidden min-h-screen border-r border-zinc-200 bg-white lg:block lg:h-screen lg:overflow-hidden dark:border-zinc-800 dark:bg-zinc-950">
                <x-app.sidebar :$club :$availableClubs :$backToClub />
            </aside>

            <div class="min-w-0 lg:h-screen lg:overflow-y-auto">
                <header class="flex h-16 items-center justify-between border-b border-zinc-200 bg-white px-4 lg:hidden dark:border-zinc-800 dark:bg-zinc-950">
                    <span class="font-semibold">{{ config('app.name') }}</span>
                    <x-app.icon-button x-on:click="navigationOpen = true" icon="bars-3" :label="__('app.navigation.open')" />
                </header>

                <main class="mx-auto grid w-full max-w-7xl gap-8 px-4 py-8 sm:px-6 lg:px-10 lg:py-12 motion-safe:animate-[fade-in_.35s_ease-out]">
                    @if ($flashStatus)
                        <x-app.alert>{{ $flashStatus }}</x-app.alert>
                    @endif
                    {{ $slot }}
                </main>
            </div>
        </div>

        <div x-cloak x-show="navigationOpen" class="fixed inset-0 z-50 lg:hidden">
            <button class="absolute inset-0 bg-zinc-950/50" x-on:click="navigationOpen = false" aria-label="{{ __('app.navigation.close') }}"></button>
            <aside class="relative h-full w-[min(20rem,88vw)] bg-white shadow-2xl dark:bg-zinc-950">
                <div class="absolute top-3 right-3">
                    <x-app.icon-button x-on:click="navigationOpen = false" icon="x-mark" :label="__('app.navigation.close')" />
                </div>
                <x-app.sidebar :$club :$availableClubs :$backToClub />
            </aside>
        </div>

        <x-app.flux-scripts />
    </body>
</html>
