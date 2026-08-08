<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Member;
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

        Member::factory()
            ->for($club)
            ->for($user)
            ->create([
                'name' => $user->name,
                'email' => $user->email,
            ]);

        Member::factory()->count(4)->for($club)->create();
    }
}
