<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Poste;
use App\Models\Operator;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING POSTES PAGE LIGNE COLUMN DISPLAY ===\n\n";

// Get a test user with tenant
$user = User::with('tenant')->first();
if (!$user) {
    echo "❌ No users found in database\n";
    exit(1);
}

// Authenticate the user
auth()->login($user);

echo "✅ Test User: {$user->name} (Tenant: {$user->tenant->name})\n\n";

// Test the exact query used in PosteController::index()
echo "🔍 Testing PosteController::index() query:\n";
echo "==========================================\n";

$postes = Poste::with('operators')->whereHas('operators')->get();
echo "📊 Found {$postes->count()} postes with operators\n\n";

foreach ($postes->take(10) as $poste) {
    echo "🏭 POSTE: {$poste->name}\n";
    echo "   Operators: {$poste->operators->count()}\n";
    
    if ($poste->operators->count() > 0) {
        echo "   Operator details:\n";
        foreach ($poste->operators as $operator) {
            $ligne = $operator->ligne ?? 'NULL';
            echo "      - {$operator->full_name} (Ligne: {$ligne})\n";
        }
        
        // Test the Blade template logic
        $lignes = $poste->operators->pluck('ligne')->filter()->unique()->sort()->values();
        echo "   Ligne display logic:\n";
        echo "      Raw lignes: " . $poste->operators->pluck('ligne')->toJson() . "\n";
        echo "      Filtered/unique: " . $lignes->toJson() . "\n";
        
        if ($lignes->count() > 0) {
            echo "      Final display: '" . $lignes->join(', ') . "'\n";
        } else {
            echo "      Final display: 'No ligne assigned'\n";
        }
    } else {
        echo "   Final display: 'No operators'\n";
    }
    
    echo "\n";
}

// Test specific scenarios
echo "🧪 TESTING SPECIFIC SCENARIOS:\n";
echo "==============================\n";

// Scenario 1: Poste with multiple operators on same ligne
echo "Scenario 1: Multiple operators on same ligne\n";
$posteWithMultiple = $postes->filter(function($poste) {
    return $poste->operators->count() > 1;
})->first();

if ($posteWithMultiple) {
    echo "   Poste: {$posteWithMultiple->name}\n";
    $lignes = $posteWithMultiple->operators->pluck('ligne')->filter()->unique()->sort()->values();
    echo "   Lignes: " . $lignes->join(', ') . "\n";
} else {
    echo "   No poste found with multiple operators\n";
}

// Scenario 2: Poste with operators on different lignes
echo "\nScenario 2: Operators on different lignes\n";
$posteWithDifferent = $postes->filter(function($poste) {
    $lignes = $poste->operators->pluck('ligne')->filter()->unique();
    return $lignes->count() > 1;
})->first();

if ($posteWithDifferent) {
    echo "   Poste: {$posteWithDifferent->name}\n";
    $lignes = $posteWithDifferent->operators->pluck('ligne')->filter()->unique()->sort()->values();
    echo "   Lignes: " . $lignes->join(', ') . "\n";
} else {
    echo "   No poste found with operators on different lignes\n";
}

// Scenario 3: Check for null/empty ligne values
echo "\nScenario 3: Operators with null/empty ligne\n";
$operatorsWithNullLigne = Operator::whereNull('ligne')->orWhere('ligne', '')->count();
echo "   Operators with null/empty ligne: {$operatorsWithNullLigne}\n";

if ($operatorsWithNullLigne > 0) {
    $posteWithNullLigne = $postes->filter(function($poste) {
        return $poste->operators->filter(function($op) {
            return empty($op->ligne);
        })->count() > 0;
    })->first();
    
    if ($posteWithNullLigne) {
        echo "   Example poste with null ligne: {$posteWithNullLigne->name}\n";
        $lignes = $posteWithNullLigne->operators->pluck('ligne')->filter()->unique()->sort()->values();
        if ($lignes->count() > 0) {
            echo "   Display: '" . $lignes->join(', ') . "'\n";
        } else {
            echo "   Display: 'No ligne assigned'\n";
        }
    }
}

echo "\n=== COMPARISON WITH OTHER PAGES ===\n";
echo "===================================\n";

// Compare with operators page display
echo "Operators page ligne display:\n";
$sampleOperators = Operator::with('poste')->take(5)->get();
foreach ($sampleOperators as $op) {
    $ligne = $op->ligne ?? 'NULL';
    echo "   {$op->full_name} -> Ligne: {$ligne}\n";
}

echo "\n=== TEST COMPLETED ===\n";
