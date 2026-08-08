@props(['title', 'description'])

<section {{ $attributes->class('grid overflow-hidden rounded-lg bg-emerald-900 text-white lg:grid-cols-2') }}>
    <div class="grid content-center gap-4 p-8 lg:p-12"><h2 class="text-3xl font-semibold tracking-tight">{{ $title }}</h2><p class="max-w-xl text-emerald-100">{{ $description }}</p>{{ $slot }}</div>
    @isset($media)<div class="min-h-64">{{ $media }}</div>@endisset
</section>
