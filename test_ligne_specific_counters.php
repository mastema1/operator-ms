<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Poste;
use App\Models\Operator;
use Illuminate\Support\Facades\Cache;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING LIGNE-SPECIFIC COUNTER LOGIC ===\n\n";

// Get a test user with tenant
$user = User::with('tenant')->first();
if (!$user) {
    echo "❌ No users found in database\n";
    exit(1);
}

echo "✅ Test User: {$user->name} (Tenant: {$user->tenant->name})\n\n";

// Authenticate the user
auth()->login($user);

// Clear cache to ensure fresh data
Cache::flush();

echo "🔍 ANALYZING CRITICAL POSTES BY LIGNE\n";
echo "=====================================\n";

// Get critical postes with operators
$criticalPostes = Poste::where('is_critical', true)
    ->with([
        'operators:id,first_name,last_name,poste_id,ligne',
        'operators.attendances' => function ($query) {
            $query->whereDate('date', today())
                  ->select('id', 'operator_id', 'date', 'status');
        }
    ])
    ->select('id', 'name')
    ->get();

echo "📊 Found {$criticalPostes->count()} critical postes\n\n";

// Simulate the new logic
$occupiedCriticalPostes = 0;
$nonOccupiedCriticalPostes = 0;
$posteLigneCombinations = collect();

echo "📋 DETAILED BREAKDOWN BY POSTE-LIGNE COMBINATIONS:\n";
echo "---------------------------------------------------\n";

foreach ($criticalPostes as $poste) {
    echo "🏢 POSTE: {$poste->name}\n";
    
    if ($poste->operators->isEmpty()) {
        echo "   ❌ No operators assigned - counting as 1 non-occupied\n";
        $nonOccupiedCriticalPostes++;
    } else {
        // Group operators by ligne
        $operatorsByLigne = $poste->operators->groupBy('ligne');
        echo "   📍 Found operators on " . $operatorsByLigne->count() . " ligne(s)\n";
        
        foreach ($operatorsByLigne as $ligne => $operatorsOnLigne) {
            $ligne = $ligne ?: 'Ligne1';
            echo "      🔸 {$ligne}: {$operatorsOnLigne->count()} operators\n";
            
            // Check attendance for operators on this ligne
            $presentOperators = $operatorsOnLigne->filter(function ($operator) {
                $todayAttendance = $operator->attendances->first();
                return !$todayAttendance || $todayAttendance->status === 'present';
            });
            
            $presentCount = $presentOperators->count();
            $absentCount = $operatorsOnLigne->count() - $presentCount;
            
            if (!$presentOperators->isEmpty()) {
                echo "         ✅ OCCUPIED ({$presentCount} present, {$absentCount} absent)\n";
                $occupiedCriticalPostes++;
            } else {
                echo "         ❌ NON-OCCUPIED (all {$absentCount} operators absent)\n";
                $nonOccupiedCriticalPostes++;
            }
            
            // Show operator details
            foreach ($operatorsOnLigne as $operator) {
                $todayAttendance = $operator->attendances->first();
                $status = !$todayAttendance || $todayAttendance->status === 'present' ? 'Present' : 'Absent';
                $statusIcon = $status === 'Present' ? '✅' : '❌';
                echo "            {$statusIcon} {$operator->first_name} {$operator->last_name} ({$status})\n";
            }
            
            $posteLigneCombinations->push([
                'poste' => $poste,
                'ligne' => $ligne,
                'operators' => $operatorsOnLigne,
                'is_occupied' => !$presentOperators->isEmpty()
            ]);
        }
    }
    echo "\n";
}

echo "📊 FINAL COUNTS (LIGNE-SPECIFIC):\n";
echo "==================================\n";
echo "✅ Occupied Critical Postes: {$occupiedCriticalPostes}\n";
echo "❌ Non-Occupied Critical Postes: {$nonOccupiedCriticalPostes}\n";
echo "📋 Total Poste-Ligne Combinations: " . ($occupiedCriticalPostes + $nonOccupiedCriticalPostes) . "\n\n";

// Compare with old logic (unique postes)
$oldOccupiedCount = $criticalPostes->filter(function ($poste) {
    if ($poste->operators->isEmpty()) {
        return false;
    }
    
    $presentOperators = $poste->operators->filter(function ($operator) {
        $todayAttendance = $operator->attendances->first();
        return !$todayAttendance || $todayAttendance->status === 'present';
    });
    
    return !$presentOperators->isEmpty();
})->count();

$oldNonOccupiedCount = $criticalPostes->filter(function ($poste) {
    if ($poste->operators->isEmpty()) {
        return true;
    }
    
    $presentOperators = $poste->operators->filter(function ($operator) {
        $todayAttendance = $operator->attendances->first();
        return !$todayAttendance || $todayAttendance->status === 'present';
    });
    
    return $presentOperators->isEmpty();
})->count();

echo "📊 COMPARISON WITH OLD LOGIC (UNIQUE POSTES):\n";
echo "==============================================\n";
echo "Old Occupied Count: {$oldOccupiedCount}\n";
echo "Old Non-Occupied Count: {$oldNonOccupiedCount}\n";
echo "Old Total: " . ($oldOccupiedCount + $oldNonOccupiedCount) . "\n\n";

echo "📈 IMPROVEMENT ANALYSIS:\n";
echo "========================\n";
$occupiedDiff = $occupiedCriticalPostes - $oldOccupiedCount;
$nonOccupiedDiff = $nonOccupiedCriticalPostes - $oldNonOccupiedCount;

if ($occupiedDiff > 0) {
    echo "✅ New logic found {$occupiedDiff} more occupied critical positions\n";
} elseif ($occupiedDiff < 0) {
    echo "⚠️  New logic found " . abs($occupiedDiff) . " fewer occupied critical positions\n";
} else {
    echo "➡️  Same occupied count (no multi-ligne critical postes found)\n";
}

if ($nonOccupiedDiff > 0) {
    echo "❌ New logic found {$nonOccupiedDiff} more non-occupied critical positions\n";
} elseif ($nonOccupiedDiff < 0) {
    echo "✅ New logic found " . abs($nonOccupiedDiff) . " fewer non-occupied critical positions\n";
} else {
    echo "➡️  Same non-occupied count\n";
}

echo "\n🎯 MULTI-LIGNE EXAMPLES FOUND:\n";
echo "===============================\n";
$multiLignePostes = $posteLigneCombinations->groupBy(function($item) {
    return $item['poste']->name;
})->filter(function($group) {
    return $group->count() > 1;
});

if ($multiLignePostes->count() > 0) {
    foreach ($multiLignePostes as $posteName => $combinations) {
        echo "🏢 {$posteName} appears on " . $combinations->count() . " ligne(s):\n";
        foreach ($combinations as $combo) {
            $status = $combo['is_occupied'] ? 'OCCUPIED' : 'NON-OCCUPIED';
            $icon = $combo['is_occupied'] ? '✅' : '❌';
            echo "   {$icon} {$combo['ligne']}: {$status} ({$combo['operators']->count()} operators)\n";
        }
        echo "\n";
    }
} else {
    echo "ℹ️  No critical postes found on multiple lignes in current data\n";
}

echo "=== TEST COMPLETED ===\n";
