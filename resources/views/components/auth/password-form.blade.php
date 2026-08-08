@props(['email'])

<x-form :action="route('login')">
    <input type="hidden" name="email" value="{{ $email }}">
    <x-form.input name="password" type="password" :label="__('auth.password_step.password')" autocomplete="current-password" required autofocus />
    <x-form.checkbox name="remember" :label="__('auth.password_step.remember')" />
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a class="text-sm font-medium text-emerald-700 hover:underline dark:text-emerald-400" href="{{ route('password.request') }}">{{ __('auth.password_step.forgot') }}</a>
        <x-app.button type="submit">{{ __('auth.password_step.submit') }}</x-app.button>
    </div>
    <a class="text-center text-sm text-zinc-600 hover:underline dark:text-zinc-300" href="{{ route('login', ['restart' => 1]) }}">{{ __('auth.password_step.different_email') }}</a>
</x-form>
