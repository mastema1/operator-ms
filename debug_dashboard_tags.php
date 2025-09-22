<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "=== Debugging Dashboard Status Tags ===\n\n";

$tenant = \App\Models\Tenant::first();
echo "🏢 Using Tenant: {$tenant->name} (ID: {$tenant->id})\n\n";

// Clear cache to ensure fresh data
\App\Services\DashboardCacheManager::clearAllCaches($tenant->id);

// Get dashboard data
$dashboardData = \App\Services\QueryOptimizationService::getDashboardData($tenant->id);

echo "📊 Dashboard Data Summary:\n";
echo "   - Occupied: {$dashboardData['occupiedCriticalPostes']}\n";
echo "   - Non-Occupied: {$dashboardData['nonOccupiedCriticalPostes']}\n";
echo "   - Total Entries: {$dashboardData['criticalPostesWithOperators']->count()}\n\n";

echo "=== Detailed Entry Analysis ===\n";
foreach ($dashboardData['criticalPostesWithOperators'] as $index => $assignment) {
    echo "Entry #" . ($index + 1) . ":\n";
    echo "   - Poste: {$assignment['poste_name']}\n";
    echo "   - Ligne: {$assignment['ligne']}\n";
    echo "   - Operator: {$assignment['operator_name']}\n";
    echo "   - Present: " . ($assignment['is_present'] ? 'Yes' : 'No') . "\n";
    echo "   - Non-occupied: " . ($assignment['is_non_occupe'] ? 'Yes' : 'No') . "\n";
    
    // Check for status tag fields
    if (isset($assignment['status_tag'])) {
        echo "   - Status Tag: {$assignment['status_tag']}\n";
    } else {
        echo "   - Status Tag: ❌ NOT SET\n";
    }
    
    if (isset($assignment['status_class'])) {
        echo "   - Status Class: {$assignment['status_class']}\n";
    } else {
        echo "   - Status Class: ❌ NOT SET\n";
    }
    
    if (isset($assignment['urgency_level'])) {
        echo "   - Urgency Level: {$assignment['urgency_level']}\n";
    } else {
        echo "   - Urgency Level: ❌ NOT SET\n";
    }
    
    echo "   - Backup Assignments: " . count($assignment['backup_assignments']) . "\n";
    echo "\n";
}

// Check if any entries have the required fields
$hasStatusTags = $dashboardData['criticalPostesWithOperators']->filter(function($assignment) {
    return isset($assignment['status_tag']) && isset($assignment['status_class']);
})->count();

echo "=== SUMMARY ===\n";
echo "📊 Entries with status tags: {$hasStatusTags} / {$dashboardData['criticalPostesWithOperators']->count()}\n";

if ($hasStatusTags === 0) {
    echo "❌ NO ENTRIES HAVE STATUS TAGS - This is the problem!\n";
    echo "\nPossible causes:\n";
    echo "   1. Data not being generated in QueryOptimizationService\n";
    echo "   2. Cache not being cleared properly\n";
    echo "   3. Logic error in status tag generation\n";
} else {
    echo "✅ Some entries have status tags\n";
}

// Test with a specific operator
echo "\n=== Testing Status Tag Generation ===\n";
$testOperator = \App\Models\Operator::where('tenant_id', $tenant->id)
    ->whereExists(function($query) use ($tenant) {
        $query->select(\DB::raw(1))
              ->from('critical_positions')
              ->whereRaw('critical_positions.poste_id = operators.poste_id')
              ->whereRaw('critical_positions.ligne = operators.ligne')
              ->where('critical_positions.is_critical', true)
              ->where('critical_positions.tenant_id', $tenant->id);
    })
    ->first();

if ($testOperator) {
    echo "Testing with operator: {$testOperator->first_name} {$testOperator->last_name}\n";
    
    // Mark as absent
    \App\Models\Attendance::updateOrCreate(
        ['operator_id' => $testOperator->id, 'date' => today()],
        ['status' => 'absent', 'tenant_id' => $tenant->id]
    );
    
    // Clear cache and get fresh data
    \App\Services\DashboardCacheManager::clearAllCaches($tenant->id);
    $freshData = \App\Services\QueryOptimizationService::getDashboardData($tenant->id);
    
    // Find this operator in the data
    foreach ($freshData['criticalPostesWithOperators'] as $assignment) {
        if ($assignment['operator_id'] == $testOperator->id) {
            echo "Found operator in fresh data:\n";
            echo "   - Status Tag: " . ($assignment['status_tag'] ?? 'NOT SET') . "\n";
            echo "   - Status Class: " . ($assignment['status_class'] ?? 'NOT SET') . "\n";
            echo "   - Urgency Level: " . ($assignment['urgency_level'] ?? 'NOT SET') . "\n";
            break;
        }
    }
    
    // Cleanup
    \App\Models\Attendance::where('operator_id', $testOperator->id)->delete();
    \App\Services\DashboardCacheManager::clearAllCaches($tenant->id);
}

echo "\n✅ Debug complete!\n";
