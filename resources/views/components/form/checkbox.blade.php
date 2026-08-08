@props(['name', 'label', 'checked' => false, 'value' => '1'])

<flux:field>
    <flux:checkbox :$name :$label :$checked :$value {{ $attributes }} />
    <x-form.error :$name />
</flux:field>
