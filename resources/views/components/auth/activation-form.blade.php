@props(['action'])

<x-form :$action>
    <x-form.input name="name" :label="__('auth.register.name')" :value="old('name')" autocomplete="name" required />
    <x-form.input name="password" type="password" :label="__('auth.register.password')" autocomplete="new-password" required />
    <x-form.input name="password_confirmation" type="password" :label="__('auth.register.password_confirmation')" autocomplete="new-password" required />
    <x-form.actions><x-app.button type="submit">{{ __('auth.activation.submit') }}</x-app.button></x-form.actions>
</x-form>
