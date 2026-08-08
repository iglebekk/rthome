@props(['title', 'description' => null])

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
    <body class="min-h-screen bg-stone-50 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <main class="grid min-h-screen place-items-center px-4 py-10 sm:px-6">
            <x-story.split-panel :$title :$description>
                {{ $slot }}
            </x-story.split-panel>
        </main>
        <x-app.flux-scripts />
    </body>
</html>
