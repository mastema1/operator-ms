<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Poste;
use App\Models\Operator;
use App\Models\Tenant;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== MULTI-TENANT ISOLATION TEST ===\n\n";

// Get all tenants and users
$tenants = Tenant::with('users')->get();
echo "📊 Found {$tenants->count()} tenants in database\n\n";

foreach ($tenants as $tenant) {
    echo "🏢 TENANT: {$tenant->name} (ID: {$tenant->id})\n";
    echo "   Users: {$tenant->users->count()}\n";
    
    if ($tenant->users->isNotEmpty()) {
        $user = $tenant->users->first();
        echo "   Test User: {$user->name}\n";
        
        // Authenticate as this user
        auth()->login($user);
        
        // Test Poste queries
        echo "\n   🔍 POSTE QUERIES:\n";
        
        // Test 1: Basic Poste::all() query
        $allPostes = Poste::all();
        echo "      Poste::all() -> {$allPostes->count()} postes\n";
        
        // Test 2: Filtered postes (like in OperatorController)
        $allowedPosteNames = [
            'Poste 1', 'Poste 2', 'Poste 3', 'Poste 4', 'Poste 5', 'Poste 6', 'Poste 7', 'Poste 8', 'Poste 9', 'Poste 10',
            'Poste 11', 'Poste 12', 'Poste 13', 'Poste 14', 'Poste 15', 'Poste 16', 'Poste 17', 'Poste 18', 'Poste 19', 'Poste 20',
            'Poste 21', 'Poste 22', 'Poste 23', 'Poste 24', 'Poste 25', 'Poste 26', 'Poste 27', 'Poste 28', 'Poste 29', 'Poste 30',
            'Poste 31', 'Poste 32', 'Poste 33', 'Poste 34', 'Poste 35', 'Poste 36', 'Poste 37', 'Poste 38', 'Poste 39', 'Poste 40',
            'ABS', 'Bol', 'Bouchon', 'CMC', 'COND', 'FILISTE', 'FILISTE EPS', 'FW', 'Polyvalent', 'Ravitailleur', 'Retouche', 'TAG', 'Team Speaker', 'VISSEUSE'
        ];
        
        $filteredPostes = Poste::query()
            ->select('id', 'name', 'is_critical')
            ->whereIn('name', $allowedPosteNames)
            ->orderByRaw("CASE WHEN name REGEXP '^Poste [0-9]+' THEN CAST(SUBSTRING(name, 7) AS UNSIGNED) ELSE 100000 END")
            ->orderBy('name')
            ->get();
        
        echo "      Filtered postes -> {$filteredPostes->count()} postes\n";
        
        // Test 3: Raw query without tenant scope
        $rawPostes = \DB::table('postes')->where('tenant_id', $user->tenant_id)->count();
        echo "      Raw DB query -> {$rawPostes} postes\n";
        
        // Test 4: Check specific postes
        $poste1 = Poste::where('name', 'Poste 1')->first();
        $abs = Poste::where('name', 'ABS')->first();
        
        echo "      Poste 1: " . ($poste1 ? "Found (ID: {$poste1->id})" : "NOT FOUND") . "\n";
        echo "      ABS: " . ($abs ? "Found (ID: {$abs->id})" : "NOT FOUND") . "\n";
        
        // Test 5: Operators for this tenant
        $operators = Operator::all();
        echo "      Operators: {$operators->count()}\n";
        
        // Show sample postes
        if ($filteredPostes->count() > 0) {
            echo "      Sample postes:\n";
            foreach ($filteredPostes->take(5) as $poste) {
                echo "         - {$poste->name} (ID: {$poste->id})\n";
            }
        }
        
        auth()->logout();
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
}

// Test cross-tenant contamination
echo "🔍 CROSS-TENANT CONTAMINATION TEST:\n";
echo "===================================\n";

$tenant1User = User::where('tenant_id', 1)->first();
$tenant2User = User::where('tenant_id', 2)->first();

if ($tenant1User && $tenant2User) {
    // Login as tenant 1 user
    auth()->login($tenant1User);
    $tenant1Postes = Poste::pluck('id')->toArray();
    auth()->logout();
    
    // Login as tenant 2 user
    auth()->login($tenant2User);
    $tenant2Postes = Poste::pluck('id')->toArray();
    auth()->logout();
    
    $overlap = array_intersect($tenant1Postes, $tenant2Postes);
    
    echo "Tenant 1 postes: " . count($tenant1Postes) . "\n";
    echo "Tenant 2 postes: " . count($tenant2Postes) . "\n";
    echo "Overlapping IDs: " . count($overlap) . "\n";
    
    if (count($overlap) > 0) {
        echo "❌ CRITICAL: Cross-tenant data contamination detected!\n";
        echo "Overlapping poste IDs: " . implode(', ', $overlap) . "\n";
    } else {
        echo "✅ Good: No cross-tenant contamination detected\n";
    }
} else {
    echo "⚠️  Cannot test cross-tenant contamination - need users from at least 2 tenants\n";
}

echo "\n=== TEST COMPLETED ===\n";
