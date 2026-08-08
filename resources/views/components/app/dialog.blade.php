@props(['name', 'title', 'description', 'confirmLabel', 'action', 'method' => 'DELETE'])

<span x-on:click="$flux.modal('{{ $name }}').show()">{{ $trigger }}</span>
<flux:modal :$name class="md:w-md">
    <div class="grid gap-5">
        <div class="grid gap-2"><flux:heading size="lg">{{ $title }}</flux:heading><flux:text>{{ $description }}</flux:text></div>
        {{ $slot }}
        <x-form :$action :$method>
            @isset($fields){{ $fields }}@endisset
            <x-form.actions>
                <x-app.button variant="ghost" x-on:click="$flux.modal('{{ $name }}').close()">{{ __('app.actions.cancel') }}</x-app.button>
                <x-app.button type="submit" variant="danger">{{ $confirmLabel }}</x-app.button>
            </x-form.actions>
        </x-form>
    </div>
</flux:modal>
