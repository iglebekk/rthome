<?php

namespace App\Actions;

use App\Models\Event;

class BuildTaktCalendarUrlAction
{
    public function handle(Event $event): string
    {
        $timezone = 'Europe/Oslo';
        $parameters = array_filter([
            'title' => $event->name,
            'start' => $event->starts_at->setTimezone($timezone)->format('Y-m-d\TH:i:s'),
            'end' => $event->ends_at->setTimezone($timezone)->format('Y-m-d\TH:i:s'),
            'timezone' => $timezone,
            'location' => $event->location,
            'description' => $event->short_description,
        ], static fn (?string $value): bool => $value !== null && $value !== '');

        return 'https://takt.on-forge.com/create?'.http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
    }
}
