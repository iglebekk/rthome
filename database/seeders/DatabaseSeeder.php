<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Event;
use App\Models\Member;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $club = Club::factory()->create([
            'name' => 'Demo Club',
        ]);

        $member = Member::factory()
            ->for($club)
            ->for($user)
            ->create([
                'name' => $user->name,
                'email' => $user->email,
            ]);

        $members = Member::factory()->count(4)->for($club)->create();

        Position::factory()->for($club)->for($member)->create([
            'name' => 'President',
            'sort_order' => 0,
        ]);
        Position::factory()->for($club)->for($members[0])->create([
            'name' => 'Treasurer',
            'sort_order' => 1,
        ]);
        Position::factory()->for($club)->for($members[1])->create([
            'name' => 'Board Member',
            'sort_order' => 2,
        ]);

        Event::factory()->count(3)->for($club)->create();
    }
}
