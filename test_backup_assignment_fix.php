<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "=== Testing Backup Assignment Fix ===\n\n";

// Get the first tenant for testing
$tenant = \App\Models\Tenant::first();
if (!$tenant) {
    echo "❌ No tenants found. Please run seeders first.\n";
    exit(1);
}

echo "🏢 Using Tenant: {$tenant->name} (ID: {$tenant->id})\n\n";

// Step 1: Create or find the "Bol" poste
$bolPoste = \App\Models\Poste::where('tenant_id', $tenant->id)
    ->where('name', 'Bol')
    ->first();

if (!$bolPoste) {
    $bolPoste = \App\Models\Poste::create([
        'name' => 'Bol',
        'tenant_id' => $tenant->id
    ]);
    echo "✅ Created 'Bol' poste\n";
} else {
    echo "✅ Found existing 'Bol' poste\n";
}

// Step 2: Create two operators assigned to "Bol" on "Ligne 1"
$operatorA = \App\Models\Operator::where('tenant_id', $tenant->id)
    ->where('poste_id', $bolPoste->id)
    ->where('ligne', 'Ligne 1')
    ->first();

if (!$operatorA) {
    $operatorA = \App\Models\Operator::create([
        'first_name' => 'Ahmed',
        'last_name' => 'TestA',
        'matricule' => 'TEST001A',
        'poste_id' => $bolPoste->id,
        'ligne' => 'Ligne 1',
        'tenant_id' => $tenant->id
    ]);
    echo "✅ Created Operator A: Ahmed TestA\n";
} else {
    echo "✅ Found existing Operator A: {$operatorA->first_name} {$operatorA->last_name}\n";
}

$operatorB = \App\Models\Operator::where('tenant_id', $tenant->id)
    ->where('poste_id', $bolPoste->id)
    ->where('ligne', 'Ligne 1')
    ->where('id', '!=', $operatorA->id)
    ->first();

if (!$operatorB) {
    $operatorB = \App\Models\Operator::create([
        'first_name' => 'Fatima',
        'last_name' => 'TestB',
        'matricule' => 'TEST001B',
        'poste_id' => $bolPoste->id,
        'ligne' => 'Ligne 1',
        'tenant_id' => $tenant->id
    ]);
    echo "✅ Created Operator B: Fatima TestB\n";
} else {
    echo "✅ Found existing Operator B: {$operatorB->first_name} {$operatorB->last_name}\n";
}

// Step 3: Create a backup operator
$backupOperator = \App\Models\Operator::where('tenant_id', $tenant->id)
    ->whereNotIn('id', [$operatorA->id, $operatorB->id])
    ->first();

if (!$backupOperator) {
    $backupOperator = \App\Models\Operator::create([
        'first_name' => 'Hassan',
        'last_name' => 'Backup',
        'matricule' => 'BACKUP001',
        'poste_id' => $bolPoste->id,
        'ligne' => 'Ligne 2', // Different ligne
        'tenant_id' => $tenant->id
    ]);
    echo "✅ Created Backup Operator: Hassan Backup\n";
} else {
    echo "✅ Found existing Backup Operator: {$backupOperator->first_name} {$backupOperator->last_name}\n";
}

// Step 4: Make the position critical
$criticalPosition = \App\Models\CriticalPosition::where('tenant_id', $tenant->id)
    ->where('poste_id', $bolPoste->id)
    ->where('ligne', 'Ligne 1')
    ->first();

if (!$criticalPosition) {
    $criticalPosition = \App\Models\CriticalPosition::create([
        'poste_id' => $bolPoste->id,
        'ligne' => 'Ligne 1',
        'is_critical' => true,
        'tenant_id' => $tenant->id
    ]);
    echo "✅ Made 'Bol on Ligne 1' a critical position\n";
} else {
    echo "✅ 'Bol on Ligne 1' is already a critical position\n";
}

// Step 5: Mark Operator A as absent
\App\Models\Attendance::updateOrCreate(
    [
        'operator_id' => $operatorA->id,
        'date' => today()
    ],
    [
        'status' => 'absent',
        'tenant_id' => $tenant->id
    ]
);
echo "✅ Marked Operator A ({$operatorA->first_name} {$operatorA->last_name}) as absent\n";

// Step 6: Mark Operator B as present
\App\Models\Attendance::updateOrCreate(
    [
        'operator_id' => $operatorB->id,
        'date' => today()
    ],
    [
        'status' => 'present',
        'tenant_id' => $tenant->id
    ]
);
echo "✅ Marked Operator B ({$operatorB->first_name} {$operatorB->last_name}) as present\n";

echo "\n=== SCENARIO SETUP COMPLETE ===\n";
echo "📋 Scenario: 'Bol' on 'Ligne 1' is a critical position with two operators:\n";
echo "   - Operator A ({$operatorA->first_name} {$operatorA->last_name}): ABSENT\n";
echo "   - Operator B ({$operatorB->first_name} {$operatorB->last_name}): PRESENT\n";
echo "   - Backup Operator ({$backupOperator->first_name} {$backupOperator->last_name}): Available\n\n";

// Step 7: Test the OLD BUG - assign backup to Operator A only
echo "=== TESTING THE FIX ===\n";

// Clear any existing backup assignments
\App\Models\BackupAssignment::where('tenant_id', $tenant->id)->delete();
echo "🧹 Cleared existing backup assignments\n";

// Assign backup to Operator A specifically (the absent one)
$backupAssignment = \App\Models\BackupAssignment::create([
    'poste_id' => $bolPoste->id,
    'operator_id' => $operatorA->id, // This is the key fix - operator-specific
    'backup_operator_id' => $backupOperator->id,
    'backup_slot' => 1,
    'assigned_date' => today(),
    'tenant_id' => $tenant->id
]);

echo "✅ Assigned backup ({$backupOperator->first_name} {$backupOperator->last_name}) to Operator A ({$operatorA->first_name} {$operatorA->last_name}) ONLY\n\n";

// Step 8: Verify the fix
echo "=== VERIFICATION ===\n";

// Check backup assignments
$operatorABackup = \App\Models\BackupAssignment::where('operator_id', $operatorA->id)
    ->whereDate('assigned_date', today())
    ->with('backupOperator')
    ->first();

$operatorBBackup = \App\Models\BackupAssignment::where('operator_id', $operatorB->id)
    ->whereDate('assigned_date', today())
    ->with('backupOperator')
    ->first();

if ($operatorABackup) {
    echo "✅ CORRECT: Operator A has backup assignment: {$operatorABackup->backupOperator->first_name} {$operatorABackup->backupOperator->last_name}\n";
} else {
    echo "❌ ERROR: Operator A should have a backup assignment but doesn't\n";
}

if (!$operatorBBackup) {
    echo "✅ CORRECT: Operator B has NO backup assignment (as expected)\n";
} else {
    echo "❌ BUG STILL EXISTS: Operator B incorrectly has backup assignment: {$operatorBBackup->backupOperator->first_name} {$operatorBBackup->backupOperator->last_name}\n";
}

// Test dashboard data
echo "\n=== DASHBOARD DATA TEST ===\n";
$dashboardData = \App\Services\QueryOptimizationService::getDashboardData($tenant->id);

$foundOperatorA = false;
$foundOperatorB = false;

foreach ($dashboardData['criticalPostesWithOperators'] as $assignment) {
    if ($assignment['poste_name'] === 'Bol' && $assignment['ligne'] === 'Ligne 1') {
        $operatorName = $assignment['operator_name'];
        $backupAssignments = $assignment['backup_assignments'];
        
        if ($operatorName === "{$operatorA->first_name} {$operatorA->last_name}") {
            $foundOperatorA = true;
            if (count($backupAssignments) > 0) {
                echo "✅ CORRECT: Dashboard shows backup for absent Operator A: {$backupAssignments[0]['operator_name']}\n";
            } else {
                echo "❌ ERROR: Dashboard should show backup for Operator A but doesn't\n";
            }
        } elseif ($operatorName === "{$operatorB->first_name} {$operatorB->last_name}") {
            $foundOperatorB = true;
            if (count($backupAssignments) === 0) {
                echo "✅ CORRECT: Dashboard shows NO backup for present Operator B\n";
            } else {
                echo "❌ BUG: Dashboard incorrectly shows backup for present Operator B: {$backupAssignments[0]['operator_name']}\n";
            }
        } else {
            // This is another operator on the same position - should have no backup
            if (count($backupAssignments) === 0) {
                echo "✅ CORRECT: Dashboard shows NO backup for other operator ({$operatorName})\n";
            } else {
                echo "❌ BUG: Dashboard incorrectly shows backup for other operator ({$operatorName}): {$backupAssignments[0]['operator_name']}\n";
            }
        }
    }
}

if (!$foundOperatorA) {
    echo "⚠️  WARNING: Operator A not found in dashboard data\n";
}
if (!$foundOperatorB) {
    echo "⚠️  WARNING: Operator B not found in dashboard data\n";
}

echo "\n=== TEST COMPLETE ===\n";
echo "🎯 The backup assignment fix ensures that backups are operator-specific,\n";
echo "   not position-specific. Only the absent operator gets the backup assignment.\n\n";

// Cleanup
echo "🧹 Cleaning up test data...\n";
\App\Models\BackupAssignment::where('tenant_id', $tenant->id)->delete();
\App\Models\Attendance::whereIn('operator_id', [$operatorA->id, $operatorB->id])->delete();

echo "✅ Cleanup complete!\n";
