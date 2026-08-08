@props(['name', 'label', 'value' => null, 'required' => false])

<x-form.input :$name :$label :$value :$required type="datetime-local" {{ $attributes }} />
