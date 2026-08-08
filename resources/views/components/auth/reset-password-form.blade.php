@props(['request'])

<x-form :action="route('password.update')">
    <input type="hidden" name="token" value="{{ $request->route('token') }}">
    <x-form.input name="email" type="email" :label="__('auth.identify.email')" :value="old('email', $request->email)" autocomplete="email" required />
    <x-form.input name="password" type="password" :label="__('auth.register.password')" autocomplete="new-password" required />
    <x-form.input name="password_confirmation" type="password" :label="__('auth.register.password_confirmation')" autocomplete="new-password" required />
    <x-form.actions><x-app.button type="submit">{{ __('auth.reset.submit') }}</x-app.button></x-form.actions>
</x-form>
