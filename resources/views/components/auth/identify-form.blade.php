<x-form :action="route('login.identify')">
    <x-form.input name="email" type="email" :label="__('auth.identify.email')" :value="old('email')" autocomplete="email" required autofocus />
    <x-form.actions class="justify-end border-0 pt-0">
        <x-app.button type="submit" icon="arrow-right">{{ __('auth.identify.submit') }}</x-app.button>
    </x-form.actions>
</x-form>
