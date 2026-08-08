<x-layouts.auth :title="__('auth.password_step.title')" :description="__('auth.password_step.description', ['email' => $email])">
    <x-auth.password-form :$email />
</x-layouts.auth>
