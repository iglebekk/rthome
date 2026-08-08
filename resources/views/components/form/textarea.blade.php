@props(['name', 'label', 'value' => null, 'rows' => 5, 'required' => false])

<flux:field>
    <flux:label>{{ $label }}</flux:label>
    <flux:textarea :$name :$rows :$required {{ $attributes }}>{{ $value }}</flux:textarea>
    <x-form.error :$name />
</flux:field>
