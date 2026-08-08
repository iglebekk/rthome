@props(['position', 'club', 'memberFirst' => false])

<x-app.card class="flex h-full items-center justify-between gap-4">
    <div class="grid gap-1">
        @if ($memberFirst && $position->member)
            <flux:heading>{{ $position->member->name }}</flux:heading>
            <flux:text>{{ $position->name }}</flux:text>
        @else
            <div class="flex flex-wrap items-center gap-2"><flux:heading>{{ $position->name }}</flux:heading><x-app.badge :color="$position->member ? 'emerald' : 'amber'">{{ $position->member?->name ?? __('positions.unfilled') }}</x-app.badge></div>
        @endif
        @if ($position->start_date || $position->end_date)<flux:text size="sm">{{ $position->start_date?->toFormattedDateString() ?? __('positions.period.open') }} – {{ $position->end_date?->toFormattedDateString() ?? __('positions.period.open') }}</flux:text>@endif
    </div>
</x-app.card>
