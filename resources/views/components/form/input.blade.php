@props(['name', 'label', 'type' => 'text', 'value' => null, 'required' => false, 'readonly' => false, 'autocomplete' => null, 'placeholder' => null, 'bag' => 'default'])

<flux:field>
    <flux:label>{{ $label }}</flux:label>
    <flux:input :$name :$type :$value :$required :$readonly :$autocomplete :$placeholder {{ $attributes }} />
    <x-form.error :$name :$bag />
</flux:field>
