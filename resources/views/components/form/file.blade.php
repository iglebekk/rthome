@props(['name', 'label', 'accept' => null])

<flux:field>
    <flux:label>{{ $label }}</flux:label>
    <flux:input :$name :$accept type="file" {{ $attributes }} />
    <x-form.error :$name />
</flux:field>
