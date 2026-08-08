@props(['name', 'label', 'options', 'value' => null, 'required' => false, 'placeholder' => null])

<flux:field>
    <flux:label>{{ $label }}</flux:label>
    <flux:select :$name :$required {{ $attributes }}>
        @if ($placeholder !== null)
            <flux:select.option value="">{{ $placeholder }}</flux:select.option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <flux:select.option :value="$optionValue" :selected="(string) $value === (string) $optionValue">{{ $optionLabel }}</flux:select.option>
        @endforeach
    </flux:select>
    <x-form.error :$name />
</flux:field>
