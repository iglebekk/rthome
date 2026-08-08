@props(['title', 'description' => null])

<section class="grid w-full max-w-5xl overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-xl shadow-zinc-900/5 lg:grid-cols-[1.05fr_.95fr] dark:border-zinc-800 dark:bg-zinc-900">
    <div class="hidden min-h-[38rem] bg-gradient-to-br from-emerald-700 via-emerald-800 to-zinc-950 p-12 text-white lg:flex lg:flex-col lg:justify-between">
        <span class="text-sm font-semibold tracking-[0.2em] uppercase">{{ config('app.name') }}</span>
        <div class="grid gap-4"><p class="max-w-md text-4xl leading-tight font-semibold tracking-tight">{{ __('app.auth_statement') }}</p><p class="max-w-sm text-emerald-100">{{ __('app.auth_supporting') }}</p></div>
    </div>
    <div class="grid content-center gap-8 p-6 sm:p-10 lg:p-12">
        <div class="grid gap-2"><x-app.heading size="xl" level="1">{{ $title }}</x-app.heading>@if ($description)<x-app.text class="text-base">{{ $description }}</x-app.text>@endif</div>
        {{ $slot }}
    </div>
</section>
