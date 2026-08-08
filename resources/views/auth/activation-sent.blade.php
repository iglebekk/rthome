<x-layouts.auth :title="__('auth.activation.sent_title')">
    <x-auth.activation-sent :email="session('activation_email', '')" />
</x-layouts.auth>
