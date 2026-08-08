<x-layouts.app :title="__('dashboard.title', ['club' => $club->name])" :$club>
    <x-app.page-header :title="__('dashboard.title', ['club' => $club->name])" :description="__('dashboard.description')" />
    <x-dashboard.overview :$club :$memberCount :$upcomingEvents :$nextEvent :$nextEventCountdown :$filledPositions :$links />
</x-layouts.app>
