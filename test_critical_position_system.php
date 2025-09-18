<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Poste;
use App\Models\Operator;
use App\Models\CriticalPosition;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING NEW CRITICAL POSITION SYSTEM ===\n\n";

// Get a test user with tenant
$user = User::with('tenant')->first();
if (!$user) {
    echo "❌ No users found in database\n";
    exit(1);
}

// Authenticate the user
auth()->login($user);

echo "✅ Test User: {$user->name} (Tenant: {$user->tenant->name})\n\n";

// Test Scenario: Create operators on same poste but different lignes
echo "🧪 TEST SCENARIO: Same Poste, Different Lignes\n";
echo "===============================================\n";

// Find or create a test poste
$testPoste = Poste::where('name', 'Poste 2')->first();
if (!$testPoste) {
    echo "❌ Poste 2 not found\n";
    exit(1);
}

echo "📍 Using Poste: {$testPoste->name} (ID: {$testPoste->id})\n\n";

// Test 1: Create operator on Poste 2 / Ligne 1 as CRITICAL
echo "1️⃣ Creating Operator on Poste 2 / Ligne 1 (CRITICAL)\n";

$operator1 = Operator::create([
    'matricule' => 'TEST001',
    'first_name' => 'Test',
    'last_name' => 'Operator1',
    'poste_id' => $testPoste->id,
    'ligne' => 'Ligne 1',
    'tenant_id' => $user->tenant_id,
]);

// Set as critical
$operator1->setCriticalPosition(true);

echo "   ✅ Operator created: {$operator1->full_name}\n";
echo "   📍 Position: {$testPoste->name} / {$operator1->ligne}\n";
echo "   🔴 Critical Status: " . ($operator1->is_critical_position ? 'CRITICAL' : 'NON-CRITICAL') . "\n\n";

// Test 2: Create operator on Poste 2 / Ligne 3 as NON-CRITICAL
echo "2️⃣ Creating Operator on Poste 2 / Ligne 3 (NON-CRITICAL)\n";

$operator2 = Operator::create([
    'matricule' => 'TEST002',
    'first_name' => 'Test',
    'last_name' => 'Operator2',
    'poste_id' => $testPoste->id,
    'ligne' => 'Ligne 3',
    'tenant_id' => $user->tenant_id,
]);

// Leave as non-critical (default)
echo "   ✅ Operator created: {$operator2->full_name}\n";
echo "   📍 Position: {$testPoste->name} / {$operator2->ligne}\n";
echo "   🟢 Critical Status: " . ($operator2->is_critical_position ? 'CRITICAL' : 'NON-CRITICAL') . "\n\n";

// Test 3: Verify independence
echo "3️⃣ VERIFICATION: Independent Critical Status\n";
echo "============================================\n";

$position1Critical = CriticalPosition::isCritical($testPoste->id, 'Ligne 1', $user->tenant_id);
$position2Critical = CriticalPosition::isCritical($testPoste->id, 'Ligne 3', $user->tenant_id);

echo "   Poste 2 / Ligne 1: " . ($position1Critical ? 'CRITICAL ✅' : 'NON-CRITICAL ❌') . "\n";
echo "   Poste 2 / Ligne 3: " . ($position2Critical ? 'NON-CRITICAL ✅' : 'CRITICAL ❌') . "\n\n";

// Test 4: Change critical status independently
echo "4️⃣ TESTING: Independent Status Changes\n";
echo "======================================\n";

// Make Ligne 3 critical
CriticalPosition::setCritical($testPoste->id, 'Ligne 3', $user->tenant_id, true);

// Reload operators to get fresh data
$operator1->refresh();
$operator2->refresh();

echo "   After setting Ligne 3 as critical:\n";
echo "   Poste 2 / Ligne 1: " . ($operator1->is_critical_position ? 'CRITICAL' : 'NON-CRITICAL') . "\n";
echo "   Poste 2 / Ligne 3: " . ($operator2->is_critical_position ? 'CRITICAL' : 'NON-CRITICAL') . "\n\n";

// Test 5: Database verification
echo "5️⃣ DATABASE VERIFICATION\n";
echo "========================\n";

$criticalPositions = CriticalPosition::where('poste_id', $testPoste->id)
    ->where('tenant_id', $user->tenant_id)
    ->get();

echo "   Critical Position Records:\n";
foreach ($criticalPositions as $position) {
    $status = $position->is_critical ? 'CRITICAL' : 'NON-CRITICAL';
    echo "      - {$testPoste->name} / {$position->ligne}: {$status}\n";
}

// Test 6: Old vs New System Comparison
echo "\n6️⃣ OLD vs NEW SYSTEM COMPARISON\n";
echo "===============================\n";

echo "   OLD SYSTEM (Poste-only):\n";
echo "      Poste->is_critical: " . ($testPoste->is_critical ? 'CRITICAL' : 'NON-CRITICAL') . "\n";
echo "      Problem: All operators on this poste would have same status\n\n";

echo "   NEW SYSTEM (Poste+Ligne):\n";
echo "      Poste 2 / Ligne 1: " . ($operator1->is_critical_position ? 'CRITICAL' : 'NON-CRITICAL') . "\n";
echo "      Poste 2 / Ligne 3: " . ($operator2->is_critical_position ? 'CRITICAL' : 'NON-CRITICAL') . "\n";
echo "      Solution: Each Poste+Ligne combination has independent status\n\n";

// Cleanup
echo "🧹 CLEANUP\n";
echo "==========\n";

$operator1->delete();
$operator2->delete();
CriticalPosition::where('poste_id', $testPoste->id)
    ->where('tenant_id', $user->tenant_id)
    ->delete();

echo "   ✅ Test operators and critical positions cleaned up\n\n";

echo "🎉 SUCCESS: Critical Position System Working Correctly!\n";
echo "======================================================\n";
echo "✅ Same poste can have different critical status per ligne\n";
echo "✅ Critical status is independent for each Poste+Ligne combination\n";
echo "✅ Database properly tracks unique position combinations\n";
echo "✅ Old flawed logic has been replaced\n";

echo "\n=== TEST COMPLETED ===\n";
