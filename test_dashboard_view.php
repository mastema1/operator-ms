<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "=== Testing Dashboard View Status Tags ===\n\n";

$tenant = \App\Models\Tenant::first();
echo "🏢 Using Tenant: {$tenant->name} (ID: {$tenant->id})\n\n";

// Clear cache
\App\Services\DashboardCacheManager::clearAllCaches($tenant->id);

// Get dashboard data
$dashboardData = \App\Services\QueryOptimizationService::getDashboardData($tenant->id);

echo "📊 Dashboard Data:\n";
echo "   - Occupied: {$dashboardData['occupiedCriticalPostes']}\n";
echo "   - Non-Occupied: {$dashboardData['nonOccupiedCriticalPostes']}\n";
echo "   - Total Entries: {$dashboardData['criticalPostesWithOperators']->count()}\n\n";

// Check if entries have status tags
$entriesWithTags = 0;
$statusTagCounts = ['URGENT' => 0, 'COVERED' => 0, 'OCCUPIED' => 0];

foreach ($dashboardData['criticalPostesWithOperators'] as $assignment) {
    if (isset($assignment['status_tag']) && isset($assignment['status_class'])) {
        $entriesWithTags++;
        $tag = $assignment['status_tag'];
        if (isset($statusTagCounts[$tag])) {
            $statusTagCounts[$tag]++;
        }
    }
}

echo "🏷️  Status Tags Analysis:\n";
echo "   - Entries with tags: {$entriesWithTags} / {$dashboardData['criticalPostesWithOperators']->count()}\n";
echo "   - URGENT: {$statusTagCounts['URGENT']}\n";
echo "   - COVERED: {$statusTagCounts['COVERED']}\n";
echo "   - OCCUPIED: {$statusTagCounts['OCCUPIED']}\n\n";

if ($entriesWithTags > 0) {
    echo "✅ Status tags are being generated correctly!\n";
    echo "📋 Sample entries with status tags:\n";
    
    $count = 0;
    foreach ($dashboardData['criticalPostesWithOperators'] as $assignment) {
        if ($count >= 3) break; // Show first 3 entries
        
        echo "   " . ($count + 1) . ". {$assignment['poste_name']} - {$assignment['operator_name']}\n";
        echo "      Status: {$assignment['status_tag']} ({$assignment['status_class']})\n";
        echo "      Present: " . ($assignment['is_present'] ? 'Yes' : 'No') . "\n";
        echo "      Urgency: {$assignment['urgency_level']}\n\n";
        
        $count++;
    }
} else {
    echo "❌ No status tags found in dashboard data!\n";
}

// Test the view rendering (simulate what the dashboard controller does)
echo "=== Testing View Data Structure ===\n";

// Simulate the controller passing data to the view
$viewData = [
    'occupiedCriticalPostes' => $dashboardData['occupiedCriticalPostes'],
    'nonOccupiedCriticalPostes' => $dashboardData['nonOccupiedCriticalPostes'],
    'criticalPostesWithOperators' => $dashboardData['criticalPostesWithOperators'],
];

echo "📊 View will receive:\n";
echo "   - \$occupiedCriticalPostes: {$viewData['occupiedCriticalPostes']}\n";
echo "   - \$nonOccupiedCriticalPostes: {$viewData['nonOccupiedCriticalPostes']}\n";
echo "   - \$criticalPostesWithOperators->count(): {$viewData['criticalPostesWithOperators']->count()}\n\n";

// Check the condition that determines which table shows
if ($viewData['criticalPostesWithOperators']->count() > 0) {
    echo "✅ Condition \$criticalPostesWithOperators->count() > 0 is TRUE\n";
    echo "✅ Dynamic table with status tags should be displayed!\n";
} else {
    echo "❌ Condition \$criticalPostesWithOperators->count() > 0 is FALSE\n";
    echo "❌ No data message will be shown instead\n";
}

echo "\n✅ Test complete!\n";
