@props(['user'])

<div class="grid gap-8 lg:grid-cols-2">
    <x-app.section :title="__('profile.profile_section')" :description="__('profile.profile_description')">
        <x-app.card>
            <x-form :action="route('user-profile-information.update')" method="PUT">
                <x-form.input name="name" :label="__('profile.fields.name')" :value="old('name', $user->name)" bag="updateProfileInformation" required />
                <x-form.input name="email" type="email" :label="__('profile.fields.email')" :value="old('email', $user->email)" bag="updateProfileInformation" required />
                <x-app.badge :color="$user->hasVerifiedEmail() ? 'emerald' : 'amber'">{{ $user->hasVerifiedEmail() ? __('profile.verified') : __('profile.unverified') }}</x-app.badge>
                <x-form.actions><x-app.button type="submit">{{ __('profile.save_profile') }}</x-app.button></x-form.actions>
            </x-form>
        </x-app.card>
    </x-app.section>

    <x-app.section :title="__('profile.password_section')" :description="__('profile.password_description')">
        <x-app.card>
            <x-form :action="route('user-password.update')" method="PUT">
                <x-form.input name="current_password" type="password" :label="__('profile.fields.current_password')" autocomplete="current-password" bag="updatePassword" required />
                <x-form.input name="password" type="password" :label="__('profile.fields.password')" autocomplete="new-password" bag="updatePassword" required />
                <x-form.input name="password_confirmation" type="password" :label="__('profile.fields.password_confirmation')" autocomplete="new-password" bag="updatePassword" required />
                <x-form.actions><x-app.button type="submit">{{ __('profile.save_password') }}</x-app.button></x-form.actions>
            </x-form>
        </x-app.card>
    </x-app.section>
</div>
