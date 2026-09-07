<?php

namespace App\Actions;

use App\Models\Club;
use App\Models\Member;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ImportMembersAction
{
    /**
     * @param  array<int, array<string, mixed>>  $members
     * @return array{created: int, updated: int}
     */
    public function handle(Club $club, array $members): array
    {
        return DB::transaction(function () use ($club, $members): array {
            $emails = array_values(array_filter(
                array_column($members, 'email'),
                fn (mixed $email): bool => is_string($email) && $email !== '',
            ));

            $existingMembers = $club->members()
                ->whereIn('email', $emails)
                ->get()
                ->keyBy('email');

            $created = 0;
            $updated = 0;

            foreach ($members as $member) {
                $email = $member['email'] ?? null;
                $existingMember = is_string($email) ? $existingMembers->get($email) : null;

                if ($existingMember instanceof Member) {
                    $updates = ['name' => $member['name']];

                    if (array_key_exists('phone', $member) && $member['phone'] !== null) {
                        $updates['phone'] = $member['phone'];
                    }

                    $existingMember->update($updates);
                    $updated++;

                    continue;
                }

                $club->members()->create(Arr::only($member, ['name', 'email', 'phone']));
                $created++;
            }

            return ['created' => $created, 'updated' => $updated];
        });
    }
}
