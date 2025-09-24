<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "=== DEBUG: COVERED Scenario Issue ===\n\n";

$tenant = \App\Models\Tenant::first();
echo "🏢 Using Tenant: {$tenant->name} (ID: {$tenant->id})\n\n";

// Get the test operator (Karim El Maskaoui)
$operator = \App\Models\Operator::where('tenant_id', $tenant->id)
    ->where('first_name', 'Karim')
    ->where('last_name', 'El Maskaoui')
    ->first();

if (!$operator) {
    echo "❌ Test operator not found\n";
    exit(1);
}

echo "👤 Test Operator: {$operator->first_name} {$operator->last_name}\n";
echo "   - ID: {$operator->id}\n";
echo "   - Poste: {$operator->poste->name} (ID: {$operator->poste_id})\n";
echo "   - Ligne: {$operator->ligne}\n\n";

// Check if this operator's position is critical
$criticalPosition = \App\Models\CriticalPosition::where('tenant_id', $tenant->id)
    ->where('poste_id', $operator->poste_id)
    ->where('ligne', $operator->ligne)
    ->where('is_critical', true)
    ->first();

echo "🔍 Critical Position Check:\n";
if ($criticalPosition) {
    echo "   ✅ Position IS critical (ID: {$criticalPosition->id})\n";
} else {
    echo "   ❌ Position is NOT critical - this is why it's not showing in dashboard!\n";
    
    // Show all critical positions for this tenant
    $allCritical = \App\Models\CriticalPosition::where('tenant_id', $tenant->id)
        ->where('is_critical', true)
        ->with('poste')
        ->get();
    
    echo "\n📋 All Critical Positions for this tenant:\n";
    foreach ($allCritical as $cp) {
        echo "   - {$cp->poste->name} on {$cp->ligne} (Poste ID: {$cp->poste_id})\n";
    }
    
    // Create critical position for this operator's position
    echo "\n🔧 Creating critical position for test...\n";
    \App\Models\CriticalPosition::create([
        'tenant_id' => $tenant->id,
        'poste_id' => $operator->poste_id,
        'ligne' => $operator->ligne,
        'is_critical' => true
    ]);
    echo "   ✅ Critical position created!\n";
}

// Check attendance
echo "\n📅 Attendance Check:\n";
$attendance = \App\Models\Attendance::where('operator_id', $operator->id)
    ->whereDate('date', today())
    ->first();

if ($attendance) {
    echo "   Status: {$attendance->status}\n";
} else {
    echo "   No attendance record (defaults to present)\n";
}

// Check backup assignment
echo "\n🔄 Backup Assignment Check:\n";
$backup = \App\Models\BackupAssignment::where('operator_id', $operator->id)
    ->whereDate('assigned_date', today())
    ->with('backupOperator')
    ->first();

if ($backup) {
    echo "   ✅ Backup assigned: {$backup->backupOperator->first_name} {$backup->backupOperator->last_name}\n";
    echo "   - Backup ID: {$backup->id}\n";
    echo "   - Slot: {$backup->backup_slot}\n";
} else {
    echo "   ❌ No backup assignment found\n";
}

// Clear cache and test again
echo "\n🔄 Clearing cache and testing dashboard data...\n";
\App\Services\DashboardCacheManager::clearAllCaches($tenant->id);
$dashboardData = \App\Services\QueryOptimizationService::getDashboardData($tenant->id);

// Look for our operator in the results
$found = false;
foreach ($dashboardData['criticalPostesWithOperators'] as $assignment) {
    if ($assignment['operator_id'] == $operator->id) {
        echo "✅ Found operator in dashboard data!\n";
        echo "   Status Tag: {$assignment['status_tag']}\n";
        echo "   Status Class: {$assignment['status_class']}\n";
        echo "   Present: " . ($assignment['is_present'] ? 'Yes' : 'No') . "\n";
        echo "   Backup Count: " . count($assignment['backup_assignments']) . "\n";
        $found = true;
        break;
    }
}

if (!$found) {
    echo "❌ Operator still not found in dashboard data\n";
    
    // Show all operators in dashboard data
    echo "\n📋 All operators in dashboard data:\n";
    foreach ($dashboardData['criticalPostesWithOperators'] as $i => $assignment) {
        echo "   " . ($i + 1) . ". {$assignment['operator_name']} (ID: {$assignment['operator_id']}) - {$assignment['status_tag']}\n";
    }
}

echo "\n✅ Debug complete!\n";
