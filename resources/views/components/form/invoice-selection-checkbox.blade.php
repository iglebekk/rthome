@props(['value', 'label'])

<label class="inline-flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
    <input type="checkbox" value="{{ $value }}" {{ $attributes->class('size-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white') }}>
    <span>{{ $label }}</span>
</label>
