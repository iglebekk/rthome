<?php

namespace App\Actions;

use App\Models\Club;
use Carbon\CarbonImmutable;
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
                $event['starts_at'] = CarbonImmutable::parse($event['starts_at'])->utc();
                $event['ends_at'] = CarbonImmutable::parse($event['ends_at'])->utc();

                $club->events()->create($event);
            }

            return count($events);
        });
    }
}
