<x-layouts.app :title="__('clubs.settings.events.title')" :$club>
    <x-app.page-header
        :title="__('clubs.settings.events.title')"
        :description="__('clubs.settings.events.description')"
        :eyebrow="$club->name"
    >
        <x-slot:actions>
            <x-app.modal
                name="event-agent-prompt"
                :title="__('clubs.settings.events.import.agent_prompt_title')"
                :trigger-label="__('clubs.settings.events.import.agent_prompt_trigger')"
            >
                <x-form.textarea
                    name="agent-prompt"
                    :label="__('clubs.settings.events.import.agent_prompt_label')"
                    :value="__('clubs.settings.events.import.agent_prompt')"
                    rows="24"
                    readonly
                />
                <x-slot:actions>
                    <x-app.button type="button" variant="ghost" x-on:click="$flux.modal('event-agent-prompt').close()">{{ __('clubs.settings.events.import.close') }}</x-app.button>
                </x-slot:actions>
            </x-app.modal>
        </x-slot:actions>
    </x-app.page-header>

    <x-app.section>
        <x-app.card>
            <x-app.section class="gap-2">
                <x-app.heading size="lg">{{ __('clubs.settings.events.import.title') }}</x-app.heading>
                <x-app.text>{{ __('clubs.settings.events.import.description') }}</x-app.text>
                <x-app.text>{{ __('clubs.settings.events.import.fields') }}</x-app.text>
                <x-app.text>{{ __('clubs.settings.events.import.timezone') }}</x-app.text>
            </x-app.section>

            <x-form :action="route('clubs.settings.events.import', $club)">
                <x-form.textarea name="json" :label="__('clubs.settings.events.import.json_label')" :value="old('json')" rows="16" required />
                <x-form.actions>
                    <x-app.button type="submit">{{ __('clubs.settings.events.import.submit') }}</x-app.button>
                </x-form.actions>
            </x-form>
        </x-app.card>
    </x-app.section>
</x-layouts.app>
