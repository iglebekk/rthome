@props(['title'])

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light dark">
        <meta name="robots" content="noindex, nofollow">
        <title>{{ $title }} · {{ config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <x-app.flux-styles />
    </head>
    <body class="min-h-screen bg-stone-50 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <main class="mx-auto grid w-full max-w-7xl gap-8 px-4 py-8 sm:px-6 lg:px-10 lg:py-12 motion-safe:animate-[fade-in_.35s_ease-out]">
            {{ $slot }}
        </main>

        <x-app.flux-scripts />
    </body>
</html>
