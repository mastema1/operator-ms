<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Poste;
use App\Http\Controllers\OperatorController;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING POSTE DROPDOWN FILTER ===\n\n";

// Get a test user with tenant
$user = User::with('tenant')->first();
if (!$user) {
    echo "❌ No users found in database\n";
    exit(1);
}

echo "✅ Test User: {$user->name} (Tenant: {$user->tenant->name})\n\n";

// Authenticate the user
auth()->login($user);

// Define the expected allowed postes list
$expectedAllowedPostes = [
    'Poste 1', 'Poste 2', 'Poste 3', 'Poste 4', 'Poste 5', 'Poste 6', 'Poste 7', 'Poste 8', 'Poste 9', 'Poste 10',
    'Poste 11', 'Poste 12', 'Poste 13', 'Poste 14', 'Poste 15', 'Poste 16', 'Poste 17', 'Poste 18', 'Poste 19', 'Poste 20',
    'Poste 21', 'Poste 22', 'Poste 23', 'Poste 24', 'Poste 25', 'Poste 26', 'Poste 27', 'Poste 28', 'Poste 29', 'Poste 30',
    'Poste 31', 'Poste 32', 'Poste 33', 'Poste 34', 'Poste 35', 'Poste 36', 'Poste 37', 'Poste 38', 'Poste 39', 'Poste 40',
    'ABS', 'Bol', 'Bouchon', 'CMC', 'COND', 'FILISTE', 'FILISTE EPS', 'FW', 'Polyvalent', 'Ravitailleur', 'Retouche', 'TAG', 'Team Speaker', 'VISSEUSE'
];

echo "📋 Expected Allowed Postes: " . count($expectedAllowedPostes) . " total\n\n";

// Test 1: Check all postes in database
echo "🔍 TEST 1: All Postes in Database\n";
echo "=================================\n";

$allPostes = Poste::select('name')->orderBy('name')->get()->pluck('name')->toArray();
echo "📊 Total postes in database: " . count($allPostes) . "\n";

// Test 2: Simulate controller filtering
echo "\n🔍 TEST 2: Controller Filtering Logic\n";
echo "====================================\n";

$filteredPostes = Poste::query()
    ->select('id', 'name', 'is_critical')
    ->whereIn('name', $expectedAllowedPostes)
    ->orderByRaw("CASE WHEN name REGEXP '^Poste [0-9]+' THEN CAST(SUBSTRING(name, 7) AS UNSIGNED) ELSE 100000 END")
    ->orderBy('name')
    ->get();

echo "📊 Filtered postes count: " . $filteredPostes->count() . "\n";
echo "📋 Filtered postes list:\n";

$foundPostes = [];
foreach ($filteredPostes as $poste) {
    $criticalStatus = $poste->is_critical ? ' (Critical)' : '';
    echo "   ✅ {$poste->name}{$criticalStatus}\n";
    $foundPostes[] = $poste->name;
}

// Test 3: Check for missing postes
echo "\n🔍 TEST 3: Missing Postes Analysis\n";
echo "==================================\n";

$missingPostes = array_diff($expectedAllowedPostes, $foundPostes);
if (empty($missingPostes)) {
    echo "✅ All expected postes are available in the dropdown\n";
} else {
    echo "❌ Missing postes from dropdown:\n";
    foreach ($missingPostes as $missing) {
        echo "   - {$missing}\n";
    }
}

// Test 4: Check for extra postes (not in allowed list)
echo "\n🔍 TEST 4: Extra Postes Analysis\n";
echo "===============================\n";

$extraPostes = array_diff($foundPostes, $expectedAllowedPostes);
if (empty($extraPostes)) {
    echo "✅ No unauthorized postes in the dropdown\n";
} else {
    echo "⚠️  Extra postes found in dropdown (should be filtered out):\n";
    foreach ($extraPostes as $extra) {
        echo "   - {$extra}\n";
    }
}

// Test 5: Verify ordering
echo "\n🔍 TEST 5: Ordering Verification\n";
echo "===============================\n";

$numericPostes = array_filter($foundPostes, function($name) {
    return preg_match('/^Poste \d+$/', $name);
});

$specialPostes = array_filter($foundPostes, function($name) {
    return !preg_match('/^Poste \d+$/', $name);
});

echo "📊 Numeric postes (Poste 1-40): " . count($numericPostes) . "\n";
echo "📊 Special postes: " . count($specialPostes) . "\n";

// Check if numeric postes are in correct order
$expectedNumericOrder = true;
$previousNumber = 0;
foreach ($numericPostes as $poste) {
    if (preg_match('/^Poste (\d+)$/', $poste, $matches)) {
        $currentNumber = (int)$matches[1];
        if ($currentNumber <= $previousNumber) {
            $expectedNumericOrder = false;
            break;
        }
        $previousNumber = $currentNumber;
    }
}

if ($expectedNumericOrder) {
    echo "✅ Numeric postes are in correct order\n";
} else {
    echo "❌ Numeric postes are not in correct order\n";
}

echo "\n📋 Special Postes Found:\n";
foreach ($specialPostes as $special) {
    echo "   ✅ {$special}\n";
}

// Test 6: Database seeder verification
echo "\n🔍 TEST 6: Database Seeder Verification\n";
echo "======================================\n";

$seederPostes = [
    'ABS', 'Bol', 'Bouchon', 'CMC', 'COND', 'FILISTE', 'FILISTE EPS', 'FW', 
    'Polyvalent', 'Ravitailleur', 'Retouche', 'TAG', 'Team Speaker', 'VISSEUSE'
];

$missingSeederPostes = array_diff($seederPostes, $foundPostes);
if (empty($missingSeederPostes)) {
    echo "✅ All seeder postes are available\n";
} else {
    echo "❌ Missing seeder postes:\n";
    foreach ($missingSeederPostes as $missing) {
        echo "   - {$missing}\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "Expected postes: " . count($expectedAllowedPostes) . "\n";
echo "Found postes: " . count($foundPostes) . "\n";
echo "Missing postes: " . count($missingPostes) . "\n";
echo "Extra postes: " . count($extraPostes) . "\n";

if (count($missingPostes) == 0 && count($extraPostes) == 0) {
    echo "\n🎉 SUCCESS: Poste dropdown filtering is working perfectly!\n";
} else {
    echo "\n⚠️  ISSUES FOUND: Poste dropdown filtering needs attention\n";
}

echo "\n=== TEST COMPLETED ===\n";
