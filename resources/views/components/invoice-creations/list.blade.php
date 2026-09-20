@props(['club', 'drafts'])

<x-app.section :title="__('invoices.drafts_title')" :description="__('invoices.drafts_description')">
    <div class="grid gap-5">
        @forelse ($drafts as $draft)
            <x-app.card class="grid gap-4 sm:grid-cols-[1fr_auto] sm:items-center">
                <div class="grid gap-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-app.heading>{{ __('invoices.draft') }}</x-app.heading>
                        <x-app.badge>{{ __('invoices.statuses.draft') }}</x-app.badge>
                    </div>
                    <x-app.text size="sm">{{ __('invoices.draft_summary', ['recipients' => $draft->recipients_count, 'lines' => $draft->lines_count]) }}</x-app.text>
                </div>
                <div class="flex items-center gap-2">
                    <x-app.link-button :href="route('clubs.invoice-creations.edit', [$club, $draft])" variant="filled">{{ __('invoices.actions.edit_draft') }}</x-app.link-button>
                    <x-app.dialog
                        :name="'delete-invoice-draft-'.$draft->getKey()"
                        :title="__('invoices.delete_draft_title')"
                        :description="__('invoices.delete_draft_description')"
                        :confirm-label="__('invoices.actions.delete_draft')"
                        :action="route('clubs.invoice-creations.destroy', [$club, $draft])"
                    >
                        <x-slot:trigger><x-app.icon-button icon="trash" :label="__('invoices.actions.delete_draft')" /></x-slot:trigger>
                    </x-app.dialog>
                </div>
            </x-app.card>
        @empty
            <x-app.text>{{ __('invoices.no_drafts') }}</x-app.text>
        @endforelse
    </div>
</x-app.section>
