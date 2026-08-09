@props(['name', 'title', 'triggerLabel'])

<x-app.button type="button" x-on:click="$flux.modal('{{ $name }}').show()">{{ $triggerLabel }}</x-app.button>

<flux:modal :$name class="w-[min(56rem,calc(100vw-2rem))]" scroll="body">
    <x-app.section class="gap-5">
        <x-app.heading size="lg">{{ $title }}</x-app.heading>
        {{ $slot }}
        @isset($actions)
            <x-form.actions>{{ $actions }}</x-form.actions>
        @endisset
    </x-app.section>
</flux:modal>
