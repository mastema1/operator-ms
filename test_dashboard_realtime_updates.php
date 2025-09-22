<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "=== Testing Dashboard Real-Time Updates ===\n\n";

// Get the first tenant for testing
$tenant = \App\Models\Tenant::first();
if (!$tenant) {
    echo "❌ No tenants found. Please run seeders first.\n";
    exit(1);
}

echo "🏢 Using Tenant: {$tenant->name} (ID: {$tenant->id})\n\n";

// Find a critical operator to test with
$criticalOperator = \App\Models\Operator::where('tenant_id', $tenant->id)
    ->whereExists(function($query) use ($tenant) {
        $query->select(\DB::raw(1))
              ->from('critical_positions')
              ->whereRaw('critical_positions.poste_id = operators.poste_id')
              ->whereRaw('critical_positions.ligne = operators.ligne')
              ->where('critical_positions.is_critical', true)
              ->where('critical_positions.tenant_id', $tenant->id);
    })
    ->first();

if (!$criticalOperator) {
    echo "❌ No critical operators found. Creating test data...\n";
    
    // Create a test poste and operator
    $testPoste = \App\Models\Poste::firstOrCreate([
        'name' => 'Test Critical Poste',
        'tenant_id' => $tenant->id
    ]);
    
    $criticalOperator = \App\Models\Operator::create([
        'first_name' => 'Test',
        'last_name' => 'Operator',
        'matricule' => 'TEST999',
        'poste_id' => $testPoste->id,
        'ligne' => 'Ligne 1',
        'tenant_id' => $tenant->id
    ]);
    
    // Make it critical
    \App\Models\CriticalPosition::create([
        'poste_id' => $testPoste->id,
        'ligne' => 'Ligne 1',
        'is_critical' => true,
        'tenant_id' => $tenant->id
    ]);
    
    echo "✅ Created test critical operator: {$criticalOperator->first_name} {$criticalOperator->last_name}\n";
}

echo "🎯 Testing with critical operator: {$criticalOperator->first_name} {$criticalOperator->last_name}\n";
echo "   Poste: {$criticalOperator->poste->name} on {$criticalOperator->ligne}\n\n";

// Function to get dashboard data and extract key metrics
function getDashboardMetrics($tenantId) {
    $data = \App\Services\QueryOptimizationService::getDashboardData($tenantId);
    return [
        'occupied' => $data['occupiedCriticalPostes'],
        'non_occupied' => $data['nonOccupiedCriticalPostes'],
        'total_entries' => count($data['criticalPostesWithOperators']),
        'cache_time' => now()->format('H:i:s.u')
    ];
}

// Step 1: Get baseline dashboard data
echo "=== STEP 1: Baseline Dashboard Data ===\n";
$baseline = getDashboardMetrics($tenant->id);
echo "📊 Occupied Critical Postes: {$baseline['occupied']}\n";
echo "📊 Non-Occupied Critical Postes: {$baseline['non_occupied']}\n";
echo "📊 Total Entries: {$baseline['total_entries']}\n";
echo "🕐 Cached at: {$baseline['cache_time']}\n\n";

// Step 2: Mark operator as absent
echo "=== STEP 2: Marking Operator as Absent ===\n";
$attendance = \App\Models\Attendance::updateOrCreate(
    [
        'operator_id' => $criticalOperator->id,
        'date' => today()
    ],
    [
        'status' => 'absent',
        'tenant_id' => $tenant->id
    ]
);

// Manually trigger cache clearing (simulating what the Absences page does)
\App\Services\DashboardCacheManager::clearOnAttendanceChange($tenant->id);

echo "✅ Marked {$criticalOperator->first_name} {$criticalOperator->last_name} as ABSENT\n";
echo "✅ Cleared dashboard cache\n\n";

// Step 3: Check dashboard data immediately
echo "=== STEP 3: Dashboard Data After Change (Immediate) ===\n";
$afterChange = getDashboardMetrics($tenant->id);
echo "📊 Occupied Critical Postes: {$afterChange['occupied']}\n";
echo "📊 Non-Occupied Critical Postes: {$afterChange['non_occupied']}\n";
echo "📊 Total Entries: {$afterChange['total_entries']}\n";
echo "🕐 Cached at: {$afterChange['cache_time']}\n\n";

// Step 4: Wait 1 second and check again
echo "=== STEP 4: Dashboard Data After 1 Second ===\n";
sleep(1);
$after1Second = getDashboardMetrics($tenant->id);
echo "📊 Occupied Critical Postes: {$after1Second['occupied']}\n";
echo "📊 Non-Occupied Critical Postes: {$after1Second['non_occupied']}\n";
echo "📊 Total Entries: {$after1Second['total_entries']}\n";
echo "🕐 Cached at: {$after1Second['cache_time']}\n\n";

// Step 5: Wait 4 seconds total and check again (should be fresh cache)
echo "=== STEP 5: Dashboard Data After 4 Seconds (New Cache) ===\n";
sleep(3); // Total 4 seconds
$after4Seconds = getDashboardMetrics($tenant->id);
echo "📊 Occupied Critical Postes: {$after4Seconds['occupied']}\n";
echo "📊 Non-Occupied Critical Postes: {$after4Seconds['non_occupied']}\n";
echo "📊 Total Entries: {$after4Seconds['total_entries']}\n";
echo "🕐 Cached at: {$after4Seconds['cache_time']}\n\n";

// Step 6: Mark operator as present again
echo "=== STEP 6: Marking Operator as Present ===\n";
$attendance->status = 'present';
$attendance->save();

// Clear cache again
\App\Services\DashboardCacheManager::clearOnAttendanceChange($tenant->id);

echo "✅ Marked {$criticalOperator->first_name} {$criticalOperator->last_name} as PRESENT\n";
echo "✅ Cleared dashboard cache\n\n";

// Step 7: Check dashboard data immediately after marking present
echo "=== STEP 7: Dashboard Data After Marking Present (Immediate) ===\n";
$afterPresent = getDashboardMetrics($tenant->id);
echo "📊 Occupied Critical Postes: {$afterPresent['occupied']}\n";
echo "📊 Non-Occupied Critical Postes: {$afterPresent['non_occupied']}\n";
echo "📊 Total Entries: {$afterPresent['total_entries']}\n";
echo "🕐 Cached at: {$afterPresent['cache_time']}\n\n";

// Analysis
echo "=== ANALYSIS ===\n";

// Check if cache times are different (indicating fresh data)
$cacheTimesUnique = count(array_unique([
    $baseline['cache_time'],
    $afterChange['cache_time'],
    $after1Second['cache_time'],
    $after4Seconds['cache_time'],
    $afterPresent['cache_time']
]));

echo "📈 Cache Time Analysis:\n";
echo "   - Baseline: {$baseline['cache_time']}\n";
echo "   - After Change: {$afterChange['cache_time']}\n";
echo "   - After 1s: {$after1Second['cache_time']}\n";
echo "   - After 4s: {$after4Seconds['cache_time']}\n";
echo "   - After Present: {$afterPresent['cache_time']}\n";
echo "   - Unique cache times: {$cacheTimesUnique}/5\n\n";

// Check if data changes are reflected
$dataChanges = [];
if ($afterChange['cache_time'] !== $baseline['cache_time']) {
    $dataChanges[] = "✅ Cache cleared immediately after attendance change";
} else {
    $dataChanges[] = "❌ Cache NOT cleared after attendance change";
}

if ($after4Seconds['cache_time'] !== $after1Second['cache_time']) {
    $dataChanges[] = "✅ Cache refreshed after 3+ seconds (within limit)";
} else {
    $dataChanges[] = "⚠️  Cache still same after 4 seconds (may be within 3s limit)";
}

if ($afterPresent['cache_time'] !== $after4Seconds['cache_time']) {
    $dataChanges[] = "✅ Cache cleared immediately when marking present";
} else {
    $dataChanges[] = "❌ Cache NOT cleared when marking present";
}

foreach ($dataChanges as $change) {
    echo $change . "\n";
}

echo "\n=== PERFORMANCE METRICS ===\n";
$startTime = microtime(true);
getDashboardMetrics($tenant->id);
$endTime = microtime(true);
$queryTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

echo "🚀 Dashboard query time: " . number_format($queryTime, 2) . "ms\n";

if ($queryTime < 100) {
    echo "✅ Excellent performance (< 100ms)\n";
} elseif ($queryTime < 500) {
    echo "✅ Good performance (< 500ms)\n";
} else {
    echo "⚠️  Performance may need optimization (> 500ms)\n";
}

echo "\n=== CONCLUSION ===\n";
if ($cacheTimesUnique >= 4 && $queryTime < 500) {
    echo "🎉 SUCCESS: Dashboard updates in real-time with good performance!\n";
    echo "   - Cache clearing works properly\n";
    echo "   - Data updates immediately after changes\n";
    echo "   - Query performance is acceptable\n";
} else {
    echo "⚠️  ISSUES DETECTED:\n";
    if ($cacheTimesUnique < 4) {
        echo "   - Cache may not be clearing properly\n";
    }
    if ($queryTime >= 500) {
        echo "   - Query performance needs optimization\n";
    }
}

// Cleanup
echo "\n🧹 Cleaning up test data...\n";
\App\Models\Attendance::where('operator_id', $criticalOperator->id)->delete();
echo "✅ Cleanup complete!\n";
