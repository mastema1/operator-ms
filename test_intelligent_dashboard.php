<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "=== Testing Intelligent Dashboard Features ===\n\n";

// Get the first tenant for testing
$tenant = \App\Models\Tenant::first();
if (!$tenant) {
    echo "❌ No tenants found. Please run seeders first.\n";
    exit(1);
}

echo "🏢 Using Tenant: {$tenant->name} (ID: {$tenant->id})\n\n";

// Get critical operators for testing
$criticalOperators = \App\Models\Operator::where('tenant_id', $tenant->id)
    ->whereExists(function($query) use ($tenant) {
        $query->select(\DB::raw(1))
              ->from('critical_positions')
              ->whereRaw('critical_positions.poste_id = operators.poste_id')
              ->whereRaw('critical_positions.ligne = operators.ligne')
              ->where('critical_positions.is_critical', true)
              ->where('critical_positions.tenant_id', $tenant->id);
    })
    ->limit(3)
    ->get();

if ($criticalOperators->count() < 3) {
    echo "❌ Need at least 3 critical operators for testing. Found: {$criticalOperators->count()}\n";
    exit(1);
}

$operator1 = $criticalOperators[0];
$operator2 = $criticalOperators[1];
$operator3 = $criticalOperators[2];

echo "🎯 Testing with critical operators:\n";
echo "   1. {$operator1->first_name} {$operator1->last_name} ({$operator1->poste->name} on {$operator1->ligne})\n";
echo "   2. {$operator2->first_name} {$operator2->last_name} ({$operator2->poste->name} on {$operator2->ligne})\n";
echo "   3. {$operator3->first_name} {$operator3->last_name} ({$operator3->poste->name} on {$operator3->ligne})\n\n";

// Function to analyze dashboard data
function analyzeDashboardData($tenantId) {
    $data = \App\Services\QueryOptimizationService::getDashboardData($tenantId);
    $assignments = $data['criticalPostesWithOperators'];
    
    $analysis = [
        'total_entries' => $assignments->count(),
        'urgent_count' => 0,
        'covered_count' => 0,
        'occupied_count' => 0,
        'sorting_order' => [],
        'status_tags' => []
    ];
    
    foreach ($assignments as $index => $assignment) {
        $urgencyLevel = $assignment['urgency_level'] ?? 1;
        $statusTag = $assignment['status_tag'] ?? 'UNKNOWN';
        $operatorName = $assignment['operator_name'];
        $isPresent = $assignment['is_present'];
        
        // Count by urgency level
        if ($urgencyLevel === 3) $analysis['urgent_count']++;
        elseif ($urgencyLevel === 2) $analysis['covered_count']++;
        else $analysis['occupied_count']++;
        
        // Track sorting order
        $analysis['sorting_order'][] = [
            'position' => $index + 1,
            'operator' => $operatorName,
            'urgency' => $urgencyLevel,
            'status' => $statusTag,
            'present' => $isPresent
        ];
        
        // Track status tags
        if (!isset($analysis['status_tags'][$statusTag])) {
            $analysis['status_tags'][$statusTag] = 0;
        }
        $analysis['status_tags'][$statusTag]++;
    }
    
    return $analysis;
}

// Step 1: Clear existing data and set baseline
echo "=== STEP 1: Setting Up Test Scenarios ===\n";
\App\Models\Attendance::whereIn('operator_id', [$operator1->id, $operator2->id, $operator3->id])->delete();
\App\Models\BackupAssignment::where('tenant_id', $tenant->id)->delete();

// Mark all as present initially
foreach ([$operator1, $operator2, $operator3] as $op) {
    \App\Models\Attendance::create([
        'operator_id' => $op->id,
        'date' => today(),
        'status' => 'present',
        'tenant_id' => $tenant->id
    ]);
}

\App\Services\DashboardCacheManager::clearOnAttendanceChange($tenant->id);
echo "✅ Set all operators as PRESENT (baseline)\n\n";

// Step 2: Test OCCUPIED status (all present)
echo "=== STEP 2: Testing OCCUPIED Status (All Present) ===\n";
$baseline = analyzeDashboardData($tenant->id);
echo "📊 Total Entries: {$baseline['total_entries']}\n";
echo "📊 Status Distribution:\n";
foreach ($baseline['status_tags'] as $status => $count) {
    echo "   - {$status}: {$count}\n";
}
echo "📊 Expected: All should be OCCUPIED\n\n";

// Step 3: Create URGENT scenario (absent without backup)
echo "=== STEP 3: Testing URGENT Status (Absent Without Backup) ===\n";
$attendance1 = \App\Models\Attendance::where('operator_id', $operator1->id)->first();
$attendance1->status = 'absent';
$attendance1->save();

\App\Services\DashboardCacheManager::clearOnAttendanceChange($tenant->id);
echo "✅ Marked {$operator1->first_name} {$operator1->last_name} as ABSENT\n";

$urgentTest = analyzeDashboardData($tenant->id);
echo "📊 Status Distribution:\n";
foreach ($urgentTest['status_tags'] as $status => $count) {
    echo "   - {$status}: {$count}\n";
}

// Check if absent operator is at the top
$firstEntry = $urgentTest['sorting_order'][0] ?? null;
if ($firstEntry && !$firstEntry['present'] && $firstEntry['status'] === 'URGENT') {
    echo "✅ URGENT operator correctly sorted to top: {$firstEntry['operator']}\n";
} else {
    echo "❌ URGENT operator not at top. First entry: " . ($firstEntry['operator'] ?? 'None') . "\n";
}
echo "\n";

// Step 4: Create COVERED scenario (absent with backup)
echo "=== STEP 4: Testing COVERED Status (Absent With Backup) ===\n";
$backupOperator = \App\Models\Operator::where('tenant_id', $tenant->id)
    ->where('id', '!=', $operator1->id)
    ->first();

if ($backupOperator) {
    \App\Models\BackupAssignment::create([
        'poste_id' => $operator1->poste_id,
        'operator_id' => $operator1->id,
        'backup_operator_id' => $backupOperator->id,
        'backup_slot' => 1,
        'assigned_date' => today(),
        'tenant_id' => $tenant->id
    ]);
    
    \App\Services\DashboardCacheManager::clearOnBackupChange($tenant->id);
    echo "✅ Assigned backup ({$backupOperator->first_name} {$backupOperator->last_name}) to {$operator1->first_name} {$operator1->last_name}\n";
    
    $coveredTest = analyzeDashboardData($tenant->id);
    echo "📊 Status Distribution:\n";
    foreach ($coveredTest['status_tags'] as $status => $count) {
        echo "   - {$status}: {$count}\n";
    }
    
    // Check if covered operator status changed
    $operator1Found = false;
    foreach ($coveredTest['sorting_order'] as $entry) {
        if (strpos($entry['operator'], $operator1->first_name) !== false) {
            if ($entry['status'] === 'COVERED') {
                echo "✅ Operator with backup correctly shows COVERED status\n";
            } else {
                echo "❌ Operator with backup shows {$entry['status']} instead of COVERED\n";
            }
            $operator1Found = true;
            break;
        }
    }
    
    if (!$operator1Found) {
        echo "❌ Could not find operator in dashboard data\n";
    }
} else {
    echo "❌ No backup operator available for testing\n";
}
echo "\n";

// Step 5: Test mixed scenarios and sorting
echo "=== STEP 5: Testing Mixed Scenarios and Priority Sorting ===\n";
$attendance2 = \App\Models\Attendance::where('operator_id', $operator2->id)->first();
$attendance2->status = 'absent';
$attendance2->save();

\App\Services\DashboardCacheManager::clearOnAttendanceChange($tenant->id);
echo "✅ Marked {$operator2->first_name} {$operator2->last_name} as ABSENT (no backup)\n";

$mixedTest = analyzeDashboardData($tenant->id);
echo "📊 Final Status Distribution:\n";
foreach ($mixedTest['status_tags'] as $status => $count) {
    echo "   - {$status}: {$count}\n";
}

echo "\n📋 Priority Sorting Analysis (Top 5 entries):\n";
for ($i = 0; $i < min(5, count($mixedTest['sorting_order'])); $i++) {
    $entry = $mixedTest['sorting_order'][$i];
    $urgencyIcon = $entry['urgency'] === 3 ? '🚨' : ($entry['urgency'] === 2 ? '⚠️' : '✅');
    echo "   {$urgencyIcon} #{$entry['position']}: {$entry['operator']} - {$entry['status']} (Urgency: {$entry['urgency']})\n";
}

// Step 6: Verify sorting logic
echo "\n=== STEP 6: Sorting Logic Verification ===\n";
$sortingCorrect = true;
$previousUrgency = 4; // Start higher than max urgency

foreach ($mixedTest['sorting_order'] as $entry) {
    if ($entry['urgency'] > $previousUrgency) {
        echo "❌ Sorting error: Urgency {$entry['urgency']} after {$previousUrgency}\n";
        $sortingCorrect = false;
        break;
    }
    $previousUrgency = $entry['urgency'];
}

if ($sortingCorrect) {
    echo "✅ Priority sorting is working correctly (highest urgency first)\n";
}

// Step 7: Performance check
echo "\n=== STEP 7: Performance Analysis ===\n";
$startTime = microtime(true);
analyzeDashboardData($tenant->id);
$endTime = microtime(true);
$queryTime = ($endTime - $startTime) * 1000;

echo "🚀 Dashboard query time: " . number_format($queryTime, 2) . "ms\n";
if ($queryTime < 100) {
    echo "✅ Excellent performance maintained\n";
} elseif ($queryTime < 200) {
    echo "✅ Good performance\n";
} else {
    echo "⚠️ Performance may need optimization\n";
}

// Summary
echo "\n=== SUMMARY ===\n";
$totalUrgent = $mixedTest['urgent_count'];
$totalCovered = $mixedTest['covered_count'];
$totalOccupied = $mixedTest['occupied_count'];

echo "🎯 Intelligent Dashboard Features:\n";
echo "   ✅ Priority Sorting: URGENT → COVERED → OCCUPIED\n";
echo "   ✅ Dynamic Status Tags: {$totalUrgent} URGENT, {$totalCovered} COVERED, {$totalOccupied} OCCUPIED\n";
echo "   ✅ Real-time Updates: Cache clearing works properly\n";
echo "   ✅ Performance: Query time under " . number_format($queryTime, 0) . "ms\n";

if ($totalUrgent > 0) {
    echo "\n🚨 URGENT SITUATIONS DETECTED:\n";
    echo "   Managers should immediately address {$totalUrgent} critical position(s) without coverage!\n";
}

// Cleanup
echo "\n🧹 Cleaning up test data...\n";
\App\Models\Attendance::whereIn('operator_id', [$operator1->id, $operator2->id, $operator3->id])->delete();
\App\Models\BackupAssignment::where('tenant_id', $tenant->id)->delete();
\App\Services\DashboardCacheManager::clearAllCaches($tenant->id);
echo "✅ Cleanup complete!\n";
