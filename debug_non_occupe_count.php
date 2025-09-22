<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "=== Debugging 'Postes Non-occupé' Count ===\n\n";

$tenant = \App\Models\Tenant::first();
if (!$tenant) {
    echo "❌ No tenants found. Please run seeders first.\n";
    exit(1);
}

echo "🏢 Using Tenant: {$tenant->name} (ID: {$tenant->id})\n\n";

// Step 1: Get all critical positions
echo "=== STEP 1: Critical Positions Analysis ===\n";
$criticalPositions = \App\Services\QueryOptimizationService::getCriticalPositions($tenant->id);
echo "📊 Total Critical Positions: {$criticalPositions->count()}\n";

foreach ($criticalPositions as $position) {
    echo "   - {$position->poste->name} on {$position->ligne} (Poste ID: {$position->poste_id})\n";
}

// Step 2: Get operators for critical positions
echo "\n=== STEP 2: Operators Analysis ===\n";
$posteIds = $criticalPositions->pluck('poste_id')->unique()->toArray();
$operators = \App\Services\QueryOptimizationService::getOperatorsWithAttendance($tenant->id, $posteIds)
    ->groupBy(['poste_id', 'ligne']);

echo "📊 Operators found for critical postes:\n";
foreach ($criticalPositions as $position) {
    $posteId = $position->poste_id;
    $ligne = $position->ligne;
    $positionOperators = $operators->get($posteId, collect())->get($ligne, collect());
    
    echo "   - {$position->poste->name} on {$ligne}: {$positionOperators->count()} operators\n";
    foreach ($positionOperators as $operator) {
        $attendance = $operator->attendances->first();
        $status = $attendance ? $attendance->status : 'present';
        echo "     * {$operator->first_name} {$operator->last_name} ({$status})\n";
    }
}

// Step 3: Get backup assignments
echo "\n=== STEP 3: Backup Assignments Analysis ===\n";
$backupAssignments = \App\Services\QueryOptimizationService::getTodayBackupAssignments($tenant->id, $posteIds);
echo "📊 Total Backup Assignments: {$backupAssignments->count()}\n";

$backupsByOperator = $backupAssignments->keyBy('operator_id');
foreach ($backupAssignments as $backup) {
    echo "   - Backup for Operator {$backup->operator_id} ({$backup->operator->first_name} {$backup->operator->last_name}): {$backup->backupOperator->first_name} {$backup->backupOperator->last_name}\n";
}

// Step 4: Current calculation logic
echo "\n=== STEP 4: Current Calculation Logic ===\n";
$occupiedCount = 0;
$nonOccupiedCount = 0;
$detailedAnalysis = [];

foreach ($criticalPositions as $position) {
    $posteId = $position->poste_id;
    $ligne = $position->ligne;
    
    if (!$position->poste) {
        continue;
    }
    
    // Get operators for this position
    $positionOperators = $operators->get($posteId, collect())->get($ligne, collect());
    
    // Current logic: Check if position has operators OR backup assignments
    $hasOperators = $positionOperators->isNotEmpty();
    $hasBackupsForPosition = false;
    
    // Check if any operators in this position have backup assignments
    foreach ($positionOperators as $operator) {
        if ($backupsByOperator->has($operator->id)) {
            $hasBackupsForPosition = true;
            break;
        }
    }
    
    $isOccupied = $hasOperators || $hasBackupsForPosition;
    
    $detailedAnalysis[] = [
        'position' => "{$position->poste->name} on {$ligne}",
        'has_operators' => $hasOperators,
        'operator_count' => $positionOperators->count(),
        'has_backups' => $hasBackupsForPosition,
        'is_occupied' => $isOccupied,
        'operators' => $positionOperators->map(function($op) use ($backupsByOperator) {
            $attendance = $op->attendances->first();
            $status = $attendance ? $attendance->status : 'present';
            $hasBackup = $backupsByOperator->has($op->id);
            return [
                'name' => "{$op->first_name} {$op->last_name}",
                'status' => $status,
                'has_backup' => $hasBackup
            ];
        })->toArray()
    ];
    
    if ($isOccupied) {
        $occupiedCount++;
    } else {
        $nonOccupiedCount++;
    }
}

echo "📊 Current Calculation Results:\n";
echo "   - Occupied: {$occupiedCount}\n";
echo "   - Non-Occupied: {$nonOccupiedCount}\n\n";

// Step 5: Detailed analysis
echo "=== STEP 5: Detailed Position Analysis ===\n";
foreach ($detailedAnalysis as $analysis) {
    $status = $analysis['is_occupied'] ? '✅ OCCUPIED' : '❌ NON-OCCUPIED';
    echo "{$status}: {$analysis['position']}\n";
    echo "   - Has Operators: " . ($analysis['has_operators'] ? 'Yes' : 'No') . " ({$analysis['operator_count']} total)\n";
    echo "   - Has Backups: " . ($analysis['has_backups'] ? 'Yes' : 'No') . "\n";
    
    if (!empty($analysis['operators'])) {
        echo "   - Operators:\n";
        foreach ($analysis['operators'] as $op) {
            $backupStatus = $op['has_backup'] ? ' (has backup)' : '';
            echo "     * {$op['name']}: {$op['status']}{$backupStatus}\n";
        }
    }
    echo "\n";
}

// Step 6: Business rule analysis
echo "=== STEP 6: Business Rule Analysis ===\n";
echo "Current logic considers a position 'Occupied' if:\n";
echo "   1. It has at least one operator assigned, OR\n";
echo "   2. Any operator in that position has a backup assignment\n\n";

echo "Potential issues with current logic:\n";
echo "   ❓ Should a position with only absent operators (no backups) be 'Non-occupied'?\n";
echo "   ❓ Should a position with absent operators but with backups be 'Occupied'?\n";
echo "   ❓ Should we consider actual attendance status, not just operator assignment?\n\n";

// Step 7: Alternative calculation approaches
echo "=== STEP 7: Alternative Calculation Approaches ===\n";

// Approach 1: Based on present operators only
$approach1_occupied = 0;
$approach1_non_occupied = 0;

foreach ($criticalPositions as $position) {
    $posteId = $position->poste_id;
    $ligne = $position->ligne;
    $positionOperators = $operators->get($posteId, collect())->get($ligne, collect());
    
    $hasPresentOperators = false;
    foreach ($positionOperators as $operator) {
        $attendance = $operator->attendances->first();
        $isPresent = !$attendance || $attendance->status === 'present';
        if ($isPresent) {
            $hasPresentOperators = true;
            break;
        }
    }
    
    if ($hasPresentOperators) {
        $approach1_occupied++;
    } else {
        $approach1_non_occupied++;
    }
}

echo "Approach 1 - Based on present operators only:\n";
echo "   - Occupied: {$approach1_occupied}\n";
echo "   - Non-Occupied: {$approach1_non_occupied}\n\n";

// Approach 2: Based on present operators OR backup coverage
$approach2_occupied = 0;
$approach2_non_occupied = 0;

foreach ($criticalPositions as $position) {
    $posteId = $position->poste_id;
    $ligne = $position->ligne;
    $positionOperators = $operators->get($posteId, collect())->get($ligne, collect());
    
    $hasPresentOperators = false;
    $hasBackupCoverage = false;
    
    foreach ($positionOperators as $operator) {
        $attendance = $operator->attendances->first();
        $isPresent = !$attendance || $attendance->status === 'present';
        
        if ($isPresent) {
            $hasPresentOperators = true;
        } elseif ($backupsByOperator->has($operator->id)) {
            $hasBackupCoverage = true;
        }
    }
    
    if ($hasPresentOperators || $hasBackupCoverage) {
        $approach2_occupied++;
    } else {
        $approach2_non_occupied++;
    }
}

echo "Approach 2 - Based on present operators OR backup coverage:\n";
echo "   - Occupied: {$approach2_occupied}\n";
echo "   - Non-Occupied: {$approach2_non_occupied}\n\n";

// Step 8: Dashboard comparison
echo "=== STEP 8: Dashboard Data Comparison ===\n";
$dashboardData = \App\Services\QueryOptimizationService::getDashboardData($tenant->id);
echo "Dashboard reports:\n";
echo "   - Occupied: {$dashboardData['occupiedCriticalPostes']}\n";
echo "   - Non-Occupied: {$dashboardData['nonOccupiedCriticalPostes']}\n\n";

echo "=== RECOMMENDATIONS ===\n";
echo "Current logic: {$occupiedCount} occupied, {$nonOccupiedCount} non-occupied\n";
echo "Approach 1: {$approach1_occupied} occupied, {$approach1_non_occupied} non-occupied\n";
echo "Approach 2: {$approach2_occupied} occupied, {$approach2_non_occupied} non-occupied\n";
echo "Dashboard: {$dashboardData['occupiedCriticalPostes']} occupied, {$dashboardData['nonOccupiedCriticalPostes']} non-occupied\n\n";

if ($occupiedCount != $dashboardData['occupiedCriticalPostes'] || $nonOccupiedCount != $dashboardData['nonOccupiedCriticalPostes']) {
    echo "⚠️  DISCREPANCY DETECTED between calculation and dashboard data!\n";
} else {
    echo "✅ Calculation matches dashboard data\n";
}

echo "\n✅ Analysis complete!\n";
