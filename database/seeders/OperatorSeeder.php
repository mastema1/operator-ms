<?php

namespace Database\Seeders;

use App\Models\Operator;
use App\Models\Poste;
use Illuminate\Database\Seeder;

class OperatorSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure we have postes to assign to
        if (Poste::count() === 0) {
            $this->call(PosteSeeder::class);
        }

        // Clear existing operators to prevent duplicates
        Operator::truncate();

        // Create 50 operators with Moroccan names
        Operator::factory()->count(50)->create();
    }
} 