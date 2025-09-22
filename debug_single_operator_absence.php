<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "=== Debugging Single Operator Absence ===\n\n";

$tenant = \App\Models\Tenant::first();
echo "🏢 Using Tenant: {$tenant->name} (ID: {$tenant->id})\n\n";

// Find a position with exactly one operator
$singleOperatorPosition = \App\Models\Operator::where('tenant_id', $tenant->id)
    ->whereExists(function($query) use ($tenant) {
        $query->select(\DB::raw(1))
              ->from('critical_positions')
              ->whereRaw('critical_positions.poste_id = operators.poste_id')
              ->whereRaw('critical_positions.ligne = operators.ligne')
              ->where('critical_positions.is_critical', true)
              ->where('critical_positions.tenant_id', $tenant->id);
    })
    ->select('poste_id', 'ligne', 'id', 'first_name', 'last_name')
    ->get()
    ->groupBy(function($operator) {
        return $operator->poste_id . '_' . $operator->ligne;
    })
    ->filter(function($operators) {
        return $operators->count() === 1;
    })
    ->first();

if (!$singleOperatorPosition || $singleOperatorPosition->isEmpty()) {
    echo "❌ No single-operator critical positions found\n";
    exit(1);
}

$operator = $singleOperatorPosition->first();
echo "🎯 Testing with single operator: {$operator->first_name} {$operator->last_name}\n";
echo "   Position: Poste ID {$operator->poste_id} on {$operator->ligne}\n\n";

// Clear existing data
\App\Models\Attendance::where('operator_id', $operator->id)->delete();
\App\Models\BackupAssignment::where('tenant_id', $tenant->id)->delete();

// Step 1: Mark operator as present
echo "=== STEP 1: Operator Present ===\n";
\App\Models\Attendance::create([
    'operator_id' => $operator->id,
    'date' => today(),
    'status' => 'present',
    'tenant_id' => $tenant->id
]);

// Clear cache completely
\App\Services\DashboardCacheManager::clearAllCaches($tenant->id);

$data1 = \App\Services\QueryOptimizationService::getDashboardData($tenant->id);
echo "📊 Dashboard: {$data1['occupiedCriticalPostes']} occupied, {$data1['nonOccupiedCriticalPostes']} non-occupied\n";

// Find this operator in the dashboard data
$found = false;
foreach ($data1['criticalPostesWithOperators'] as $assignment) {
    if ($assignment['operator_id'] == $operator->id) {
        echo "✅ Found operator in dashboard:\n";
        echo "   - Name: {$assignment['operator_name']}\n";
        echo "   - Present: " . ($assignment['is_present'] ? 'Yes' : 'No') . "\n";
        echo "   - Status: {$assignment['status_tag']}\n";
        $found = true;
        break;
    }
}
if (!$found) {
    echo "❌ Operator not found in dashboard data\n";
}

// Step 2: Mark operator as absent
echo "\n=== STEP 2: Operator Absent ===\n";
$attendance = \App\Models\Attendance::where('operator_id', $operator->id)->first();
$attendance->status = 'absent';
$attendance->save();

// Clear cache completely
\App\Services\DashboardCacheManager::clearAllCaches($tenant->id);

$data2 = \App\Services\QueryOptimizationService::getDashboardData($tenant->id);
echo "📊 Dashboard: {$data2['occupiedCriticalPostes']} occupied, {$data2['nonOccupiedCriticalPostes']} non-occupied\n";

// Find this operator in the dashboard data
$found = false;
foreach ($data2['criticalPostesWithOperators'] as $assignment) {
    if ($assignment['operator_id'] == $operator->id) {
        echo "✅ Found operator in dashboard:\n";
        echo "   - Name: {$assignment['operator_name']}\n";
        echo "   - Present: " . ($assignment['is_present'] ? 'Yes' : 'No') . "\n";
        echo "   - Status: {$assignment['status_tag']}\n";
        echo "   - Urgency: {$assignment['urgency_level']}\n";
        echo "   - Non-occupied flag: " . ($assignment['is_non_occupe'] ? 'Yes' : 'No') . "\n";
        $found = true;
        break;
    }
}
if (!$found) {
    echo "❌ Operator not found in dashboard data\n";
}

// Step 3: Debug the calculation logic manually
echo "\n=== STEP 3: Manual Calculation Debug ===\n";

// Get the critical position
$criticalPosition = \App\Models\CriticalPosition::where('tenant_id', $tenant->id)
    ->where('poste_id', $operator->poste_id)
    ->where('ligne', $operator->ligne)
    ->where('is_critical', true)
    ->with('poste')
    ->first();

if (!$criticalPosition) {
    echo "❌ Critical position not found\n";
    exit(1);
}

echo "Critical position: {$criticalPosition->poste->name} on {$criticalPosition->ligne}\n";

// Get operators for this position
$posteIds = [$operator->poste_id];
$operators = \App\Services\QueryOptimizationService::getOperatorsWithAttendance($tenant->id, $posteIds)
    ->groupBy(['poste_id', 'ligne']);

$positionOperators = $operators->get($operator->poste_id, collect())->get($operator->ligne, collect());
echo "Operators found: {$positionOperators->count()}\n";

foreach ($positionOperators as $op) {
    $attendance = $op->attendances->first();
    $status = $attendance ? $attendance->status : 'present';
    echo "   - {$op->first_name} {$op->last_name}: {$status}\n";
}

// Get backup assignments
$backupAssignments = \App\Services\QueryOptimizationService::getTodayBackupAssignments($tenant->id, $posteIds)
    ->keyBy('operator_id');
echo "Backup assignments: {$backupAssignments->count()}\n";

// Apply the logic
$hasOperators = $positionOperators->isNotEmpty();
$hasPresentOperators = false;
$hasBackupCoverage = false;

foreach ($positionOperators as $op) {
    $attendance = $op->attendances->first();
    $isPresent = !$attendance || $attendance->status === 'present';
    
    echo "   - {$op->first_name} {$op->last_name}: present=" . ($isPresent ? 'true' : 'false') . "\n";
    
    if ($isPresent) {
        $hasPresentOperators = true;
    } elseif ($backupAssignments->has($op->id)) {
        $hasBackupCoverage = true;
    }
}

$isOccupied = $hasPresentOperators || $hasBackupCoverage;

echo "\nCalculation results:\n";
echo "   - Has operators: " . ($hasOperators ? 'Yes' : 'No') . "\n";
echo "   - Has present operators: " . ($hasPresentOperators ? 'Yes' : 'No') . "\n";
echo "   - Has backup coverage: " . ($hasBackupCoverage ? 'Yes' : 'No') . "\n";
echo "   - Is occupied: " . ($isOccupied ? 'Yes' : 'No') . "\n";

if (!$isOccupied) {
    echo "✅ Position should be NON-OCCUPIED\n";
} else {
    echo "❌ Position is considered OCCUPIED\n";
}

// Cleanup
\App\Models\Attendance::where('operator_id', $operator->id)->delete();
echo "\n✅ Cleanup complete!\n";
