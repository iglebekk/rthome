<?php

namespace App\Actions;

use App\Models\Club;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ImportEventsAction
{
    /**
     * @param  array<int, array<string, mixed>>  $events
     */
    public function handle(Club $club, array $events): int
    {
        return DB::transaction(function () use ($club, $events): int {
            foreach ($events as $event) {
                $payload = Arr::only($event, [
                    'name',
                    'location',
                    'starts_at',
                    'ends_at',
                    'registration_url',
                    'short_description',
                ]);

                $payload['starts_at'] = CarbonImmutable::parse($payload['starts_at'])->utc();
                $payload['ends_at'] = CarbonImmutable::parse($payload['ends_at'])->utc();

                $club->events()->create($payload);
            }

            return count($events);
        });
    }
}
