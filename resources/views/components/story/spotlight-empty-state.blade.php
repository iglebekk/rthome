@props(['title', 'description'])

<x-app.empty-state :$title :$description icon="sparkles" class="bg-gradient-to-br from-white to-emerald-50 py-20 dark:from-zinc-900 dark:to-emerald-950/30">
    @isset($action)
        <x-slot:action>{{ $action }}</x-slot:action>
    @endisset
</x-app.empty-state>
