<div class="grid gap-5">
    @if (session('status') === 'verification-link-sent')<x-app.alert>{{ __('auth.verify.resent') }}</x-app.alert>@endif
    <x-form :action="route('verification.send')">
        <x-form.actions class="justify-start border-0 pt-0"><x-app.button type="submit">{{ __('auth.verify.submit') }}</x-app.button></x-form.actions>
    </x-form>
    <x-form :action="route('logout')"><x-app.button type="submit" variant="ghost">{{ __('auth.logout') }}</x-app.button></x-form>
</div>
