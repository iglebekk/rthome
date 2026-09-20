@props(['member'])

<x-app.card class="grid gap-3 sm:grid-cols-[1fr_auto] sm:items-center" data-invoice-recipient data-recipient-id="{{ $member->getKey() }}">
    <div class="grid gap-1">
        <x-app.heading size="sm">{{ $member->name }}</x-app.heading>
        <x-app.text size="sm">{{ $member->email }}</x-app.text>
    </div>
    <x-form.hidden name="recipients[]" :value="$member->getKey()" />
    <x-app.button type="button" variant="ghost" icon="trash" data-remove-invoice-recipient>
        {{ __('invoices.actions.remove_recipient') }}
    </x-app.button>
</x-app.card>
