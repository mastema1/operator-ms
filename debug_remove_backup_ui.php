<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "=== DEBUG: Remove Backup UI Issue ===\n\n";

// Test with different users
$users = \App\Models\User::take(3)->get();

foreach ($users as $user) {
    echo "👤 Testing User: {$user->email} (ID: {$user->id}, Tenant: {$user->tenant_id})\n";
    
    // Simulate login
    auth()->login($user);
    
    // Get dashboard data for this user
    $dashboardData = \App\Services\QueryOptimizationService::getDashboardData($user->tenant_id);
    
    echo "   📊 Dashboard entries: {$dashboardData['criticalPostesWithOperators']->count()}\n";
    
    // Check for backup assignments
    $backupCount = 0;
    $posteIds = [];
    
    foreach ($dashboardData['criticalPostesWithOperators'] as $assignment) {
        if (count($assignment['backup_assignments']) > 0) {
            $backupCount++;
            $posteIds[] = $assignment['poste_id'];
        }
    }
    
    echo "   🔄 Entries with backups: {$backupCount}\n";
    echo "   📋 Poste IDs with backups: " . implode(', ', array_unique($posteIds)) . "\n";
    
    // Check for duplicate poste IDs (this is the root of the problem)
    $allPosteIds = [];
    foreach ($dashboardData['criticalPostesWithOperators'] as $assignment) {
        $allPosteIds[] = $assignment['poste_id'];
    }
    
    $duplicatePosteIds = array_diff_assoc($allPosteIds, array_unique($allPosteIds));
    if (!empty($duplicatePosteIds)) {
        echo "   ⚠️  DUPLICATE POSTE IDs FOUND: " . implode(', ', array_unique($duplicatePosteIds)) . "\n";
        echo "   🚨 This causes the rowIndex logic to fail!\n";
    } else {
        echo "   ✅ No duplicate poste IDs\n";
    }
    
    echo "\n";
}

echo "=== ANALYSIS ===\n";
echo "The removeBackup() JavaScript function uses this flawed logic:\n";
echo "1. It finds posteId from the clicked element\n";
echo "2. It searches all backup popovers and matches by posteId\n";
echo "3. It uses the forEach INDEX as rowIndex\n\n";

echo "🚨 PROBLEMS:\n";
echo "1. Multiple operators can have the same posteId (same poste, different operators)\n";
echo "2. forEach index ≠ actual table row index\n";
echo "3. Different users have different table structures\n";
echo "4. The logic finds the FIRST match, which may not be the correct row\n\n";

echo "✅ SOLUTION:\n";
echo "Instead of using posteId matching, we should:\n";
echo "1. Use the backup assignment ID to find the exact element\n";
echo "2. Traverse up to find the actual table row\n";
echo "3. Get the real row index from the table structure\n";
echo "4. Or better yet, update the UI directly using the backup ID\n\n";

echo "🔧 This explains why it works for user ID 1 but not others:\n";
echo "- User 1 might have a simpler table structure\n";
echo "- User 1 might not have duplicate poste IDs\n";
echo "- The flawed logic happens to work by coincidence for user 1\n";

echo "\n✅ Debug complete!\n";
