<x-layouts.app :title="__('dashboard.title', ['club' => $club->name])" :$club>
    <x-app.page-header :title="__('dashboard.title', ['club' => $club->name])" :description="__('dashboard.description')">
        <x-slot:actions>
            <x-app.dropdown data-test="dashboard-quick-actions">
                <x-slot:trigger>
                    <x-app.button variant="ghost" icon:trailing="chevron-down" data-test="dashboard-quick-actions-trigger">
                        {{ __('dashboard.quick_actions') }}
                    </x-app.button>
                </x-slot:trigger>
                <x-slot:menu>
                    <x-app.menu data-test="dashboard-quick-actions-menu">
                        <x-app.menu-item :href="route('clubs.members.create', $club)" icon="user-plus" data-test="dashboard-quick-action-member">
                            {{ __('members.actions.create') }}
                        </x-app.menu-item>
                        <x-app.menu-item :href="route('clubs.events.create', $club)" icon="calendar-days" data-test="dashboard-quick-action-event">
                            {{ __('events.actions.create') }}
                        </x-app.menu-item>
                        <x-app.menu-item :href="route('clubs.links.create', $club)" icon="link" data-test="dashboard-quick-action-link">
                            {{ __('links.actions.create') }}
                        </x-app.menu-item>
                    </x-app.menu>
                </x-slot:menu>
            </x-app.dropdown>
        </x-slot:actions>
    </x-app.page-header>
    <x-dashboard.overview :$club :$memberCount :$upcomingEvents :$nextEvent :$nextEventCountdown :$filledPositions :$links />
</x-layouts.app>
