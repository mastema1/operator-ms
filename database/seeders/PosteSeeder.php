<?php

namespace Database\Seeders;

use App\Models\Poste;
use Illuminate\Database\Seeder;

class PosteSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data to prevent duplicates
        Poste::truncate();

        // Create Poste 1 through Poste 40
        for ($i = 1; $i <= 40; $i++) {
            Poste::create([
                'name' => 'Poste ' . $i,
                'is_critical' => false,
            ]);
        }

        // Create additional specific postes
        $specificPostes = [
            'Polyvalent',
            'Bol',
            'FW',
            'ABS',
            'VISSEUSE',
            'TAG',
            'Retouche',
            'Ravitailleur',
            'Qualité',
            'POLY'
        ];

        foreach ($specificPostes as $posteName) {
            Poste::create([
                'name' => $posteName,
                'is_critical' => false,
            ]);
        }
    }
}