@props(['email' => null])

<x-form :action="route('register')">
    <x-form.input name="name" :label="__('auth.register.name')" :value="old('name')" autocomplete="name" required />
    <x-form.input name="email" type="email" :label="__('auth.register.email')" :value="old('email', $email)" autocomplete="email" required />
    <x-form.input name="password" type="password" :label="__('auth.register.password')" autocomplete="new-password" required />
    <x-form.input name="password_confirmation" type="password" :label="__('auth.register.password_confirmation')" autocomplete="new-password" required />
    <x-form.actions><x-app.button type="submit">{{ __('auth.register.submit') }}</x-app.button></x-form.actions>
    <a class="text-center text-sm text-zinc-600 hover:underline dark:text-zinc-300" href="{{ route('login', ['restart' => 1]) }}">{{ __('auth.register.sign_in') }}</a>
</x-form>
