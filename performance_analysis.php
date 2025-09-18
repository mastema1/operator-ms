<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Poste;
use App\Models\Operator;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

echo "=== OPERATOR MANAGEMENT SYSTEM - PERFORMANCE ANALYSIS ===\n\n";

// Current system state
echo "1. CURRENT SYSTEM STATE:\n";
echo "   - Operators: " . Operator::count() . "\n";
echo "   - Postes: " . Poste::count() . "\n";
echo "   - Attendance Records: " . Attendance::count() . "\n";
echo "   - Database: " . config('database.default') . "\n\n";

// Architecture analysis
echo "2. ARCHITECTURE ANALYSIS:\n";
echo "   - Framework: Laravel 12.x\n";
echo "   - Frontend: Blade Templates + Livewire\n";
echo "   - Database: " . (config('database.default') === 'sqlite' ? 'SQLite' : 'MySQL') . "\n";
echo "   - Server: WAMP (Windows/Apache/MySQL/PHP)\n\n";

// Key operations performance test
echo "3. KEY OPERATIONS PERFORMANCE TEST:\n\n";

// Dashboard query performance
$start = microtime(true);
$criticalPostes = Poste::where('is_critical', true)
    ->with(['operators.attendances' => function ($query) {
        $query->whereDate('date', today());
    }])
    ->get();
$dashboardTime = (microtime(true) - $start) * 1000;
echo "   Dashboard Query: {$dashboardTime}ms\n";

// Operators index performance
$start = microtime(true);
$operators = Operator::with('poste')->paginate(15);
$operatorsTime = (microtime(true) - $start) * 1000;
echo "   Operators Index: {$operatorsTime}ms\n";

// Absences query performance
$start = microtime(true);
$absences = Operator::with(['poste', 'attendances' => function ($q) {
    $q->whereDate('date', today());
}])->paginate(15);
$absencesTime = (microtime(true) - $start) * 1000;
echo "   Absences Query: {$absencesTime}ms\n";

// Postes index performance
$start = microtime(true);
$postes = Poste::with('operators')->paginate(15);
$postesTime = (microtime(true) - $start) * 1000;
echo "   Postes Index: {$postesTime}ms\n\n";

// Database query analysis
echo "4. DATABASE QUERY ANALYSIS:\n";
DB::enableQueryLog();

// Execute dashboard logic
$criticalPostes = Poste::where('is_critical', true)
    ->with(['operators.attendances' => function ($query) {
        $query->whereDate('date', today());
    }])
    ->get();

$queries = DB::getQueryLog();
echo "   Dashboard Queries: " . count($queries) . "\n";
foreach ($queries as $i => $query) {
    echo "   Query " . ($i + 1) . ": " . round($query['time'], 2) . "ms\n";
}
echo "\n";

// Memory usage
echo "5. MEMORY USAGE:\n";
echo "   Current Memory: " . round(memory_get_usage() / 1024 / 1024, 2) . " MB\n";
echo "   Peak Memory: " . round(memory_get_peak_usage() / 1024 / 1024, 2) . " MB\n";
echo "   Memory Limit: " . ini_get('memory_limit') . "\n\n";

// Potential bottlenecks
echo "6. IDENTIFIED POTENTIAL BOTTLENECKS:\n";
echo "   - N+1 Query Problem: " . (count($queries) > 3 ? "POTENTIAL ISSUE" : "OK") . "\n";
echo "   - Attendance Queries: Daily filtering without indexes\n";
echo "   - Dashboard Complexity: Multiple nested relationships\n";
echo "   - SQLite Limitations: Single-threaded, file-based\n";
echo "   - No Query Caching: Fresh queries on each request\n\n";

echo "7. PERFORMANCE THRESHOLDS (ESTIMATED):\n";
echo "   - Current Load: ~50 operators, 50 postes, minimal attendance\n";
echo "   - Warning Threshold: 500+ operators, 1000+ daily attendance records\n";
echo "   - Critical Threshold: 2000+ operators, 10000+ attendance records\n";
echo "   - SQLite Limit: ~100MB database size, ~1000 concurrent reads\n\n";

echo "Analysis complete. See recommendations in performance_recommendations.txt\n";
