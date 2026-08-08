@props(['club', 'event' => null])

<x-app.card>
    <x-form :action="$event ? route('clubs.events.update', [$club, $event]) : route('clubs.events.store', $club)" :method="$event ? 'PUT' : 'POST'" enctype="multipart/form-data">
        <x-form.input name="name" :label="__('events.fields.name')" :value="old('name', $event?->name)" required autofocus />
        <div class="grid gap-5 sm:grid-cols-2">
            <x-form.date-time name="starts_at" :label="__('events.fields.starts_at')" :value="old('starts_at', $event?->starts_at?->format('Y-m-d\TH:i'))" required />
            <x-form.date-time name="ends_at" :label="__('events.fields.ends_at')" :value="old('ends_at', $event?->ends_at?->format('Y-m-d\TH:i'))" required />
        </div>
        <x-form.input name="location" :label="__('events.fields.location')" :value="old('location', $event?->location)" />
        <x-form.input name="registration_url" type="url" :label="__('events.fields.registration_url')" :value="old('registration_url', $event?->registration_url)" />
        <x-form.textarea name="short_description" :label="__('events.fields.description')" :value="old('short_description', $event?->short_description)" />
        @if ($event?->imageUrl())<img src="{{ $event->imageUrl() }}" alt="" class="max-h-64 w-full rounded-lg object-cover">@endif
        <x-form.file name="image" :label="__('events.fields.image')" accept="image/jpeg,image/png,image/webp" />
        @if ($event?->image_path)<x-form.checkbox name="remove_image" :label="__('events.fields.remove_image')" :checked="old('remove_image', false)" />@endif
        <x-form.actions>
            <x-app.link-button :href="route('clubs.events.index', $club)" variant="ghost">{{ __('app.actions.cancel') }}</x-app.link-button>
            <x-app.button type="submit">{{ __('app.actions.save') }}</x-app.button>
        </x-form.actions>
    </x-form>
</x-app.card>
