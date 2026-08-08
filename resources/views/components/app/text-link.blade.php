@props(['href'])

<a href="{{ $href }}" {{ $attributes->class('text-sm font-medium text-emerald-700 underline decoration-emerald-300 underline-offset-4 hover:decoration-emerald-700 dark:text-emerald-400 dark:decoration-emerald-600 dark:hover:decoration-emerald-400') }}>
    {{ $slot }}
</a>
