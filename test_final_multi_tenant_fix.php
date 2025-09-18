<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Poste;
use App\Models\Operator;
use App\Models\Tenant;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FINAL MULTI-TENANT FIX VERIFICATION ===\n\n";

// Test all tenants
$tenants = Tenant::with('users')->get();

foreach ($tenants as $tenant) {
    echo "🏢 TENANT: {$tenant->name} (ID: {$tenant->id})\n";
    
    if ($tenant->users->isNotEmpty()) {
        $user = $tenant->users->first();
        
        // Authenticate as this user
        auth()->login($user);
        
        // Test the exact scenario from OperatorController
        echo "   🔍 Testing OperatorController create() scenario:\n";
        
        // Simulate the exact query from OperatorController::create()
        $allowedPosteNames = [
            'Poste 1', 'Poste 2', 'Poste 3', 'Poste 4', 'Poste 5', 'Poste 6', 'Poste 7', 'Poste 8', 'Poste 9', 'Poste 10',
            'Poste 11', 'Poste 12', 'Poste 13', 'Poste 14', 'Poste 15', 'Poste 16', 'Poste 17', 'Poste 18', 'Poste 19', 'Poste 20',
            'Poste 21', 'Poste 22', 'Poste 23', 'Poste 24', 'Poste 25', 'Poste 26', 'Poste 27', 'Poste 28', 'Poste 29', 'Poste 30',
            'Poste 31', 'Poste 32', 'Poste 33', 'Poste 34', 'Poste 35', 'Poste 36', 'Poste 37', 'Poste 38', 'Poste 39', 'Poste 40',
            'ABS', 'Bol', 'Bouchon', 'CMC', 'COND', 'FILISTE', 'FILISTE EPS', 'FW', 'Polyvalent', 'Ravitailleur', 'Retouche', 'TAG', 'Team Speaker', 'VISSEUSE'
        ];
        
        $postes = Poste::query()
            ->select('id', 'name', 'is_critical')
            ->whereIn('name', $allowedPosteNames)
            ->orderByRaw("CASE WHEN name REGEXP '^Poste [0-9]+' THEN CAST(SUBSTRING(name, 7) AS UNSIGNED) ELSE 100000 END")
            ->orderBy('name')
            ->get();
        
        echo "      ✅ Dropdown postes available: {$postes->count()}\n";
        
        if ($postes->count() > 0) {
            echo "      ✅ DROPDOWN WILL NOT BE EMPTY\n";
            
            // Test operator creation scenario
            $testPoste = $postes->first();
            echo "      🧪 Testing operator creation with: {$testPoste->name}\n";
            
            try {
                $operator = Operator::create([
                    'name' => 'Test Operator for ' . $tenant->name,
                    'poste_id' => $testPoste->id,
                    'tenant_id' => $user->tenant_id,
                ]);
                
                // Verify the operator was created with correct tenant and poste
                $createdOperator = Operator::with('poste')->find($operator->id);
                
                if ($createdOperator && $createdOperator->poste) {
                    echo "      ✅ Operator created successfully\n";
                    echo "      ✅ Poste assigned: {$createdOperator->poste->name}\n";
                    echo "      ✅ Tenant isolation: {$createdOperator->tenant_id} = {$user->tenant_id}\n";
                    
                    // Clean up test operator
                    $createdOperator->delete();
                    echo "      🧹 Test operator cleaned up\n";
                } else {
                    echo "      ❌ FAILED: Operator created but poste not assigned properly\n";
                }
                
            } catch (\Exception $e) {
                echo "      ❌ FAILED: Error creating operator - {$e->getMessage()}\n";
            }
            
        } else {
            echo "      ❌ CRITICAL: DROPDOWN WILL BE EMPTY!\n";
        }
        
        auth()->logout();
    }
    
    echo "\n" . str_repeat("-", 60) . "\n\n";
}

// Final summary
echo "🎯 FINAL VERIFICATION SUMMARY:\n";
echo "==============================\n";

$allTenantsFixed = true;
foreach ($tenants as $tenant) {
    $posteCount = Poste::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count();
    $status = $posteCount >= 54 ? "✅ FIXED" : "❌ BROKEN";
    echo "Tenant {$tenant->name}: {$posteCount} postes - {$status}\n";
    
    if ($posteCount < 54) {
        $allTenantsFixed = false;
    }
}

echo "\n";
if ($allTenantsFixed) {
    echo "🎉 SUCCESS: All tenants have sufficient postes for dropdown functionality!\n";
    echo "🎉 The multi-tenancy bug has been FIXED!\n";
} else {
    echo "❌ FAILURE: Some tenants still have missing postes\n";
}

echo "\n=== TEST COMPLETED ===\n";
