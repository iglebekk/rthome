<x-layouts.app :title="__('members.import.title')" :$club>
    <x-app.page-header
        :title="__('members.import.title')"
        :description="__('members.import.description')"
        :eyebrow="$club->name"
    >
        <x-slot:actions>
            <x-app.modal
                name="member-agent-prompt"
                :title="__('members.import.agent_prompt_title')"
                :trigger-label="__('members.import.agent_prompt_trigger')"
            >
                <x-form.textarea
                    name="agent-prompt"
                    :label="__('members.import.agent_prompt_label')"
                    :value="__('members.import.agent_prompt')"
                    rows="22"
                    readonly
                />
                <x-slot:actions>
                    <x-app.button type="button" variant="ghost" x-on:click="$flux.modal('member-agent-prompt').close()">{{ __('members.import.close') }}</x-app.button>
                </x-slot:actions>
            </x-app.modal>
        </x-slot:actions>
    </x-app.page-header>

    <x-app.section>
        <x-app.card>
            <x-app.section class="gap-2">
                <x-app.heading size="lg">{{ __('members.import.title') }}</x-app.heading>
                <x-app.text>{{ __('members.import.description') }}</x-app.text>
                <x-app.text>{{ __('members.import.fields') }}</x-app.text>
            </x-app.section>

            <x-form :action="route('clubs.settings.members.import', $club)">
                <x-form.textarea name="json" :label="__('members.import.json_label')" :value="old('json')" rows="16" required />
                <x-form.actions>
                    <x-app.button type="submit">{{ __('members.import.submit') }}</x-app.button>
                </x-form.actions>
            </x-form>
        </x-app.card>
    </x-app.section>
</x-layouts.app>
