@props(['email'])

<x-app.alert>{{ __('auth.activation.sent_description', ['email' => $email]) }}</x-app.alert>
