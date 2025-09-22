<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "=== Debugging Backup Status Logic ===\n\n";

$tenant = \App\Models\Tenant::first();
$operator = \App\Models\Operator::where('tenant_id', $tenant->id)->first();

echo "🎯 Testing with operator: {$operator->first_name} {$operator->last_name} (ID: {$operator->id})\n\n";

// Clear existing data
\App\Models\Attendance::where('operator_id', $operator->id)->delete();
\App\Models\BackupAssignment::where('tenant_id', $tenant->id)->delete();

// Mark operator as absent
\App\Models\Attendance::create([
    'operator_id' => $operator->id,
    'date' => today(),
    'status' => 'absent',
    'tenant_id' => $tenant->id
]);

echo "✅ Marked operator as ABSENT\n";

// Create backup assignment
$backupOperator = \App\Models\Operator::where('tenant_id', $tenant->id)
    ->where('id', '!=', $operator->id)
    ->first();

$backup = \App\Models\BackupAssignment::create([
    'poste_id' => $operator->poste_id,
    'operator_id' => $operator->id,
    'backup_operator_id' => $backupOperator->id,
    'backup_slot' => 1,
    'assigned_date' => today(),
    'tenant_id' => $tenant->id
]);

echo "✅ Created backup assignment (ID: {$backup->id})\n";
echo "   - Operator being replaced: {$operator->first_name} {$operator->last_name} (ID: {$operator->id})\n";
echo "   - Backup operator: {$backupOperator->first_name} {$backupOperator->last_name} (ID: {$backupOperator->id})\n\n";

// Clear cache
\App\Services\DashboardCacheManager::clearAllCaches($tenant->id);

// Test the backup assignments query
echo "=== Testing Backup Assignments Query ===\n";
$posteIds = [$operator->poste_id];
$backupAssignments = \App\Services\QueryOptimizationService::getTodayBackupAssignments($tenant->id, $posteIds);

echo "Found {$backupAssignments->count()} backup assignments:\n";
foreach ($backupAssignments as $ba) {
    echo "   - ID: {$ba->id}, Operator ID: {$ba->operator_id}, Backup ID: {$ba->backup_operator_id}\n";
}

$backupsByOperator = $backupAssignments->keyBy('operator_id');
echo "\nBackups keyed by operator_id:\n";
foreach ($backupsByOperator as $operatorId => $ba) {
    echo "   - Operator {$operatorId}: Backup {$ba->backup_operator_id}\n";
}

// Test if our operator has a backup
$operatorBackup = $backupsByOperator->get($operator->id);
if ($operatorBackup) {
    echo "\n✅ Found backup for operator {$operator->id}: {$operatorBackup->backupOperator->first_name} {$operatorBackup->backupOperator->last_name}\n";
} else {
    echo "\n❌ No backup found for operator {$operator->id}\n";
}

// Test the full dashboard data
echo "\n=== Testing Dashboard Data ===\n";
$dashboardData = \App\Services\QueryOptimizationService::getDashboardData($tenant->id);

foreach ($dashboardData['criticalPostesWithOperators'] as $assignment) {
    if ($assignment['operator_id'] == $operator->id) {
        echo "Found operator in dashboard data:\n";
        echo "   - Name: {$assignment['operator_name']}\n";
        echo "   - Present: " . ($assignment['is_present'] ? 'Yes' : 'No') . "\n";
        echo "   - Backup assignments: " . count($assignment['backup_assignments']) . "\n";
        echo "   - Status tag: {$assignment['status_tag']}\n";
        echo "   - Urgency level: {$assignment['urgency_level']}\n";
        
        if (!empty($assignment['backup_assignments'])) {
            echo "   - Backup details:\n";
            foreach ($assignment['backup_assignments'] as $backup) {
                echo "     * {$backup['operator_name']}\n";
            }
        }
        break;
    }
}

// Cleanup
\App\Models\Attendance::where('operator_id', $operator->id)->delete();
\App\Models\BackupAssignment::where('tenant_id', $tenant->id)->delete();
echo "\n✅ Cleanup complete!\n";
