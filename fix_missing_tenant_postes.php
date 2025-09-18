<?php

require_once 'vendor/autoload.php';

use App\Models\Poste;
use App\Models\Tenant;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FIXING MISSING TENANT POSTES ===\n\n";

// Get all tenants
$tenants = Tenant::all();
echo "📊 Found {$tenants->count()} tenants\n\n";

foreach ($tenants as $tenant) {
    echo "🏢 Processing Tenant: {$tenant->name} (ID: {$tenant->id})\n";
    
    // Check existing postes for this tenant
    $existingPostes = Poste::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count();
    echo "   Current postes: {$existingPostes}\n";
    
    if ($existingPostes == 0) {
        echo "   ❌ No postes found - creating required postes...\n";
        
        // Create Poste 1 through Poste 40
        for ($i = 1; $i <= 40; $i++) {
            Poste::create([
                'name' => 'Poste ' . $i,
                'is_critical' => false,
                'tenant_id' => $tenant->id,
            ]);
        }
        echo "   ✅ Created Poste 1-40\n";

        // Create additional specific postes
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
                'is_critical' => false,
                'tenant_id' => $tenant->id,
            ]);
        }
        echo "   ✅ Created " . count($specificPostes) . " special postes\n";
        
        $newCount = Poste::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count();
        echo "   📊 Total postes now: {$newCount}\n";
        
    } else {
        echo "   ✅ Tenant already has postes\n";
        
        // Check if all required postes exist
        $requiredPostes = [];
        for ($i = 1; $i <= 40; $i++) {
            $requiredPostes[] = 'Poste ' . $i;
        }
        $requiredPostes = array_merge($requiredPostes, [
            'ABS', 'Bol', 'Bouchon', 'CMC', 'COND', 'FILISTE', 'FILISTE EPS', 'FW',
            'Polyvalent', 'Ravitailleur', 'Retouche', 'TAG', 'Team Speaker', 'VISSEUSE'
        ]);
        
        $existingPosteNames = Poste::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->pluck('name')
            ->toArray();
            
        $missingPostes = array_diff($requiredPostes, $existingPosteNames);
        
        if (!empty($missingPostes)) {
            echo "   ⚠️  Missing " . count($missingPostes) . " postes, creating them...\n";
            foreach ($missingPostes as $posteName) {
                Poste::create([
                    'name' => $posteName,
                    'is_critical' => false,
                    'tenant_id' => $tenant->id,
                ]);
            }
            echo "   ✅ Created missing postes\n";
        }
    }
    
    echo "\n";
}

// Verify the fix
echo "🔍 VERIFICATION:\n";
echo "================\n";

foreach ($tenants as $tenant) {
    $posteCount = Poste::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count();
    echo "Tenant {$tenant->name}: {$posteCount} postes\n";
}

echo "\n=== COMPLETED ===\n";
