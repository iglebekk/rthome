<x-form :action="route('password.email')">
    @if (session('status'))<x-app.alert>{{ session('status') }}</x-app.alert>@endif
    <x-form.input name="email" type="email" :label="__('auth.identify.email')" :value="old('email')" autocomplete="email" required autofocus />
    <x-form.actions><x-app.button type="submit">{{ __('auth.forgot.submit') }}</x-app.button></x-form.actions>
    <a class="text-center text-sm text-zinc-600 hover:underline dark:text-zinc-300" href="{{ route('login', ['restart' => 1]) }}">{{ __('auth.forgot.back') }}</a>
</x-form>
