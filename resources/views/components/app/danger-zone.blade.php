@props(['title', 'description'])

<section class="grid gap-4 rounded-lg border border-red-200 bg-red-50/70 p-5 dark:border-red-900/70 dark:bg-red-950/20">
    <div class="grid gap-1">
        <flux:heading class="text-red-800 dark:text-red-200">{{ $title }}</flux:heading>
        <flux:text class="text-red-700 dark:text-red-300">{{ $description }}</flux:text>
    </div>
    {{ $slot }}
</section>
