<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "=== Debug Dashboard Backup Processing ===\n\n";

$tenant = \App\Models\Tenant::first();
echo "🏢 Using Tenant: {$tenant->name} (ID: {$tenant->id})\n\n";

// Create test data
$bolPoste = \App\Models\Poste::where('tenant_id', $tenant->id)->where('name', 'Bol')->first();
$operatorA = \App\Models\Operator::where('tenant_id', $tenant->id)
    ->where('poste_id', $bolPoste->id)
    ->where('ligne', 'Ligne 1')
    ->first();
$backupOperator = \App\Models\Operator::where('tenant_id', $tenant->id)
    ->where('id', '!=', $operatorA->id)
    ->first();

// Clear and create backup assignment
\App\Models\BackupAssignment::where('tenant_id', $tenant->id)->delete();
$backupAssignment = \App\Models\BackupAssignment::create([
    'poste_id' => $bolPoste->id,
    'operator_id' => $operatorA->id,
    'backup_operator_id' => $backupOperator->id,
    'backup_slot' => 1,
    'assigned_date' => today(),
    'tenant_id' => $tenant->id
]);

echo "✅ Created backup assignment: {$backupOperator->first_name} {$backupOperator->last_name} for {$operatorA->first_name} {$operatorA->last_name}\n\n";

// Debug the QueryOptimizationService step by step
echo "=== Step 1: Get Critical Positions ===\n";
$criticalPositions = \App\Services\QueryOptimizationService::getCriticalPositions($tenant->id);
echo "Found {$criticalPositions->count()} critical positions\n";
foreach ($criticalPositions as $pos) {
    if ($pos->poste_id == $bolPoste->id && $pos->ligne == 'Ligne 1') {
        echo "✅ Found critical position: {$pos->poste->name} on {$pos->ligne}\n";
    }
}

echo "\n=== Step 2: Get Operators ===\n";
$posteIds = $criticalPositions->pluck('poste_id')->unique()->toArray();
$operators = \App\Services\QueryOptimizationService::getOperatorsWithAttendance($tenant->id, $posteIds);
echo "Found {$operators->count()} operators\n";
$operatorsGrouped = $operators->groupBy(['poste_id', 'ligne']);
$bolOperators = $operatorsGrouped->get($bolPoste->id, collect())->get('Ligne 1', collect());
echo "Operators on Bol Ligne 1: {$bolOperators->count()}\n";
foreach ($bolOperators as $op) {
    echo "  - {$op->first_name} {$op->last_name} (ID: {$op->id})\n";
}

echo "\n=== Step 3: Get Backup Assignments ===\n";
$backupAssignments = \App\Services\QueryOptimizationService::getTodayBackupAssignments($tenant->id, $posteIds);
echo "Found {$backupAssignments->count()} backup assignments\n";
foreach ($backupAssignments as $backup) {
    echo "  - Backup ID {$backup->id}: Operator {$backup->operator_id} -> Backup {$backup->backup_operator_id}\n";
    echo "    Operator: {$backup->operator->first_name} {$backup->operator->last_name}\n";
    echo "    Backup: {$backup->backupOperator->first_name} {$backup->backupOperator->last_name}\n";
}

$backupsByOperator = $backupAssignments->keyBy('operator_id');
echo "Backup assignments keyed by operator_id:\n";
foreach ($backupsByOperator as $operatorId => $backup) {
    echo "  - Operator {$operatorId}: {$backup->backupOperator->first_name} {$backup->backupOperator->last_name}\n";
}

echo "\n=== Step 4: Process Dashboard Data ===\n";
foreach ($criticalPositions as $position) {
    if ($position->poste_id == $bolPoste->id && $position->ligne == 'Ligne 1') {
        echo "Processing position: {$position->poste->name} on {$position->ligne}\n";
        
        $positionOperators = $operatorsGrouped->get($position->poste_id, collect())->get($position->ligne, collect());
        echo "Position operators: {$positionOperators->count()}\n";
        
        foreach ($positionOperators as $operator) {
            echo "  Processing operator: {$operator->first_name} {$operator->last_name} (ID: {$operator->id})\n";
            
            $operatorBackup = $backupsByOperator->get($operator->id);
            if ($operatorBackup) {
                echo "    ✅ Found backup: {$operatorBackup->backupOperator->first_name} {$operatorBackup->backupOperator->last_name}\n";
            } else {
                echo "    ❌ No backup found for this operator\n";
            }
        }
    }
}

echo "\n=== Full Dashboard Data ===\n";
$dashboardData = \App\Services\QueryOptimizationService::getDashboardData($tenant->id);
foreach ($dashboardData['criticalPostesWithOperators'] as $assignment) {
    if ($assignment['poste_name'] === 'Bol' && $assignment['ligne'] === 'Ligne 1') {
        echo "Dashboard entry: {$assignment['operator_name']}\n";
        echo "  Backup assignments: " . count($assignment['backup_assignments']) . "\n";
        foreach ($assignment['backup_assignments'] as $backup) {
            echo "    - {$backup['operator_name']}\n";
        }
    }
}

// Cleanup
\App\Models\BackupAssignment::where('tenant_id', $tenant->id)->delete();
echo "\n✅ Cleanup complete!\n";
