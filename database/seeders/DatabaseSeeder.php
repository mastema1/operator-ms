<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed base user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Ensure a 'test' login user exists
        User::updateOrCreate(
            ['email' => 'test@local'],
            [
                'name' => 'test',
                'password' => bcrypt('12345678'),
            ]
        );

        // Seed domain data
        $this->call([
            PosteSeeder::class,
            OperatorSeeder::class,
        ]);

        // Seed attendance data for the last 30 days
        \App\Models\Attendance::factory()->count(50)->create();
    }
}
