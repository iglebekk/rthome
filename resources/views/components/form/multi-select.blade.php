@props(['name', 'label', 'options', 'values' => [], 'required' => false])

<flux:field>
    <flux:label>{{ $label }}</flux:label>
    <flux:select :$name :$required multiple {{ $attributes }}>
        @foreach ($options as $optionValue => $optionLabel)
            <flux:select.option :value="$optionValue" :selected="in_array((string) $optionValue, array_map('strval', $values), true)">{{ $optionLabel }}</flux:select.option>
        @endforeach
    </flux:select>
    <x-form.error :$name />
</flux:field>
