<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create demo tenants
        $tenant1 = Tenant::create(['name' => 'Demo Company A']);
        $tenant2 = Tenant::create(['name' => 'Demo Company B']);

        // Seed base users with tenants
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'tenant_id' => $tenant1->id,
        ]);

        // Ensure a 'test' login user exists
        User::updateOrCreate(
            ['email' => 'test@local'],
            [
                'name' => 'test',
                'password' => bcrypt('12345678'),
                'tenant_id' => $tenant1->id,
            ]
        );

        // Create a second demo user for tenant 2
        User::factory()->create([
            'name' => 'Demo User 2',
            'email' => 'demo2@example.com',
            'tenant_id' => $tenant2->id,
        ]);

        // Seed domain data for both tenants
        $this->call([
            PosteSeeder::class,
            CsvOperatorSeeder::class,
        ]);

        // Seed attendance data for the last 30 days
        \App\Models\Attendance::factory()->count(50)->create();
    }
}
