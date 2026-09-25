<x-layouts.public :title="__('events.public_sharing.page_title', ['club' => $club->name])">
    <x-app.page-header
        :title="$club->name"
        :description="__('events.public_sharing.public_description')"
        :eyebrow="__('events.public_sharing.eyebrow')"
    />
    <x-public-events.list :$club :$upcomingEvents />
</x-layouts.public>
