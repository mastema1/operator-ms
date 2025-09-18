<?php

namespace Database\Seeders;

use App\Models\Poste;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class PosteSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data to prevent duplicates
        Poste::truncate();

        // Get all tenants to create postes for each
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            // Create Poste 1 through Poste 40 for each tenant
            for ($i = 1; $i <= 40; $i++) {
                Poste::create([
                    'name' => 'Poste ' . $i,
                    'tenant_id' => $tenant->id,
                ]);
            }

            // Create additional specific postes for each tenant
            $specificPostes = [
                'ABS',
                'Bol',
                'Bouchon',
                'CMC',
                'COND',
                'FILISTE',
                'FILISTE EPS',
                'FW',
                'Polyvalent',
                'Ravitailleur',
                'Retouche',
                'TAG',
                'Team Speaker',
                'VISSEUSE'
            ];

            foreach ($specificPostes as $posteName) {
                Poste::create([
                    'name' => $posteName,
                    'tenant_id' => $tenant->id,
                ]);
            }
        }
    }
}