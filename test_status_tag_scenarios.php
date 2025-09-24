<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "=== COMPREHENSIVE AUDIT: Dashboard Status Tags ===\n\n";

$tenant = \App\Models\Tenant::first();
echo "🏢 Using Tenant: {$tenant->name} (ID: {$tenant->id})\n\n";

// Clear cache to ensure fresh data
\App\Services\DashboardCacheManager::clearAllCaches($tenant->id);

// Get some operators to test with
$operators = \App\Models\Operator::where('tenant_id', $tenant->id)->limit(3)->get();

if ($operators->count() < 3) {
    echo "❌ Need at least 3 operators for comprehensive testing\n";
    exit(1);
}

echo "👥 Test Operators:\n";
foreach ($operators as $i => $op) {
    echo "   " . ($i + 1) . ". {$op->first_name} {$op->last_name} - {$op->poste->name} on {$op->ligne}\n";
}
echo "\n";

// Test Scenario 1: URGENT (Absent operator with no backup)
echo "=== SCENARIO 1: URGENT (Absent + No Backup) ===\n";
$operator1 = $operators[0];

// Set operator as absent
\App\Models\Attendance::updateOrCreate(
    [
        'operator_id' => $operator1->id,
        'date' => today(),
        'tenant_id' => $tenant->id
    ],
    ['status' => 'absent']
);

// Remove any existing backup assignments
\App\Models\BackupAssignment::where('operator_id', $operator1->id)
    ->whereDate('assigned_date', today())
    ->delete();

echo "✅ Set {$operator1->first_name} {$operator1->last_name} as ABSENT with NO backup\n";

// Test Scenario 2: COVERED (Absent operator with backup)
echo "\n=== SCENARIO 2: COVERED (Absent + Has Backup) ===\n";
$operator2 = $operators[1];
$backupOperator = $operators[2]; // Use third operator as backup

// Set operator as absent
\App\Models\Attendance::updateOrCreate(
    [
        'operator_id' => $operator2->id,
        'date' => today(),
        'tenant_id' => $tenant->id
    ],
    ['status' => 'absent']
);

// Assign backup
\App\Models\BackupAssignment::updateOrCreate(
    [
        'operator_id' => $operator2->id,
        'assigned_date' => today(),
        'tenant_id' => $tenant->id
    ],
    [
        'poste_id' => $operator2->poste_id,
        'backup_operator_id' => $backupOperator->id,
        'backup_slot' => 1
    ]
);

echo "✅ Set {$operator2->first_name} {$operator2->last_name} as ABSENT with backup ({$backupOperator->first_name} {$backupOperator->last_name})\n";

// Test Scenario 3: OCCUPIED (Present operator)
echo "\n=== SCENARIO 3: OCCUPIED (Present) ===\n";
$operator3 = $operators[2];

// Set operator as present (or remove attendance record to default to present)
\App\Models\Attendance::where('operator_id', $operator3->id)
    ->whereDate('date', today())
    ->delete();

echo "✅ Set {$operator3->first_name} {$operator3->last_name} as PRESENT (default)\n";

// Clear cache and get fresh dashboard data
echo "\n=== TESTING DASHBOARD LOGIC ===\n";
\App\Services\DashboardCacheManager::clearAllCaches($tenant->id);
$dashboardData = \App\Services\QueryOptimizationService::getDashboardData($tenant->id);

echo "📊 Dashboard Results:\n";
echo "   - Total Entries: {$dashboardData['criticalPostesWithOperators']->count()}\n";

// Analyze each test operator
$testResults = [];
foreach ($operators as $i => $testOp) {
    $found = false;
    foreach ($dashboardData['criticalPostesWithOperators'] as $assignment) {
        if ($assignment['operator_id'] == $testOp->id) {
            $testResults[$i + 1] = [
                'operator' => $testOp,
                'assignment' => $assignment,
                'found' => true
            ];
            $found = true;
            break;
        }
    }
    if (!$found) {
        $testResults[$i + 1] = [
            'operator' => $testOp,
            'assignment' => null,
            'found' => false
        ];
    }
}

// Verify each scenario
echo "\n=== VERIFICATION RESULTS ===\n";

// Scenario 1: Should be URGENT
echo "🔍 SCENARIO 1 VERIFICATION (Should be URGENT):\n";
if (isset($testResults[1]) && $testResults[1]['found']) {
    $assignment = $testResults[1]['assignment'];
    $expected = 'URGENT';
    $actual = $assignment['status_tag'];
    $expectedClass = 'bg-red-500 text-white animate-pulse';
    $actualClass = $assignment['status_class'];
    
    echo "   Operator: {$testResults[1]['operator']->first_name} {$testResults[1]['operator']->last_name}\n";
    echo "   Expected: {$expected} | Actual: {$actual} " . ($expected === $actual ? "✅" : "❌") . "\n";
    echo "   Expected Class: {$expectedClass}\n";
    echo "   Actual Class: {$actualClass} " . ($expectedClass === $actualClass ? "✅" : "❌") . "\n";
    echo "   Present: " . ($assignment['is_present'] ? 'Yes' : 'No') . " (Should be No) " . (!$assignment['is_present'] ? "✅" : "❌") . "\n";
    echo "   Backup Count: " . count($assignment['backup_assignments']) . " (Should be 0) " . (count($assignment['backup_assignments']) === 0 ? "✅" : "❌") . "\n";
} else {
    echo "   ❌ Operator not found in dashboard data\n";
}

echo "\n🔍 SCENARIO 2 VERIFICATION (Should be COVERED):\n";
if (isset($testResults[2]) && $testResults[2]['found']) {
    $assignment = $testResults[2]['assignment'];
    $expected = 'COVERED';
    $actual = $assignment['status_tag'];
    $expectedClass = 'bg-yellow-500 text-white';
    $actualClass = $assignment['status_class'];
    
    echo "   Operator: {$testResults[2]['operator']->first_name} {$testResults[2]['operator']->last_name}\n";
    echo "   Expected: {$expected} | Actual: {$actual} " . ($expected === $actual ? "✅" : "❌") . "\n";
    echo "   Expected Class: {$expectedClass}\n";
    echo "   Actual Class: {$actualClass} " . ($expectedClass === $actualClass ? "✅" : "❌") . "\n";
    echo "   Present: " . ($assignment['is_present'] ? 'Yes' : 'No') . " (Should be No) " . (!$assignment['is_present'] ? "✅" : "❌") . "\n";
    echo "   Backup Count: " . count($assignment['backup_assignments']) . " (Should be 1) " . (count($assignment['backup_assignments']) === 1 ? "✅" : "❌") . "\n";
} else {
    echo "   ❌ Operator not found in dashboard data\n";
}

echo "\n🔍 SCENARIO 3 VERIFICATION (Should be OCCUPIED):\n";
if (isset($testResults[3]) && $testResults[3]['found']) {
    $assignment = $testResults[3]['assignment'];
    $expected = 'OCCUPIED';
    $actual = $assignment['status_tag'];
    $expectedClass = 'bg-green-500 text-white';
    $actualClass = $assignment['status_class'];
    
    echo "   Operator: {$testResults[3]['operator']->first_name} {$testResults[3]['operator']->last_name}\n";
    echo "   Expected: {$expected} | Actual: {$actual} " . ($expected === $actual ? "✅" : "❌") . "\n";
    echo "   Expected Class: {$expectedClass}\n";
    echo "   Actual Class: {$actualClass} " . ($expectedClass === $actualClass ? "✅" : "❌") . "\n";
    echo "   Present: " . ($assignment['is_present'] ? 'Yes' : 'No') . " (Should be Yes) " . ($assignment['is_present'] ? "✅" : "❌") . "\n";
} else {
    echo "   ❌ Operator not found in dashboard data\n";
}

// Overall status tag distribution
echo "\n=== OVERALL STATUS TAG DISTRIBUTION ===\n";
$statusCounts = ['URGENT' => 0, 'COVERED' => 0, 'OCCUPIED' => 0];
foreach ($dashboardData['criticalPostesWithOperators'] as $assignment) {
    if (isset($assignment['status_tag'])) {
        $tag = $assignment['status_tag'];
        if (isset($statusCounts[$tag])) {
            $statusCounts[$tag]++;
        }
    }
}

echo "📊 Status Tag Counts:\n";
echo "   - URGENT: {$statusCounts['URGENT']}\n";
echo "   - COVERED: {$statusCounts['COVERED']}\n";
echo "   - OCCUPIED: {$statusCounts['OCCUPIED']}\n";
echo "   - Total: " . array_sum($statusCounts) . "\n";

// Check sorting (URGENT should be first)
echo "\n=== PRIORITY SORTING VERIFICATION ===\n";
$firstEntry = $dashboardData['criticalPostesWithOperators']->first();
if ($firstEntry && isset($firstEntry['urgency_level'])) {
    echo "🔝 First entry urgency level: {$firstEntry['urgency_level']} (Should be 3 for URGENT)\n";
    echo "   Status: {$firstEntry['status_tag']}\n";
    echo "   Operator: {$firstEntry['operator_name']}\n";
    
    if ($firstEntry['urgency_level'] == 3) {
        echo "   ✅ Highest urgency entries are sorted first!\n";
    } else {
        echo "   ❌ Sorting may not be working correctly\n";
    }
} else {
    echo "❌ No entries found or missing urgency level\n";
}

echo "\n=== AUDIT COMPLETE ===\n";
echo "✅ All three scenarios have been tested!\n";
echo "📋 Check the verification results above to ensure all logic is working correctly.\n";
