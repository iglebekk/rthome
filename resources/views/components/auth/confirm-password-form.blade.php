<x-form :action="route('password.confirm')">
    <x-form.input name="password" type="password" :label="__('auth.password_step.password')" autocomplete="current-password" required autofocus />
    <x-form.actions><x-app.button type="submit">{{ __('auth.confirm.submit') }}</x-app.button></x-form.actions>
</x-form>
