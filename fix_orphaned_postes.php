<?php

require_once 'vendor/autoload.php';

use App\Models\Operator;
use App\Models\Poste;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FIXING ORPHANED POSTE REFERENCES ===\n\n";

// Find operators with invalid poste references
$orphanedOperators = Operator::whereDoesntHave('poste')->get();

echo "Found {$orphanedOperators->count()} operators with orphaned poste references:\n\n";

if ($orphanedOperators->isEmpty()) {
    echo "✅ No orphaned operators found!\n";
    exit(0);
}

foreach ($orphanedOperators as $operator) {
    echo "❌ Operator ID: {$operator->id}, Name: {$operator->first_name} {$operator->last_name}, ";
    echo "Invalid Poste ID: {$operator->poste_id}, Tenant: {$operator->tenant_id}\n";
}

echo "\n=== FIXING ORPHANED REFERENCES ===\n\n";

$fixedCount = 0;

foreach ($orphanedOperators as $operator) {
    // Get a valid poste for this tenant (preferably Poste 1 or first available)
    $validPoste = Poste::where('tenant_id', $operator->tenant_id)
        ->orderByRaw("CASE WHEN name = 'Poste 1' THEN 1 ELSE 2 END")
        ->orderBy('name')
        ->first();
    
    if ($validPoste) {
        $oldPosteId = $operator->poste_id;
        $operator->poste_id = $validPoste->id;
        $operator->save();
        
        echo "✅ Fixed Operator {$operator->id} ({$operator->first_name} {$operator->last_name}): ";
        echo "Changed poste_id from {$oldPosteId} to {$validPoste->id} ({$validPoste->name})\n";
        
        $fixedCount++;
    } else {
        echo "❌ Could not fix Operator {$operator->id}: No valid postes found for tenant {$operator->tenant_id}\n";
    }
}

echo "\n=== CLEANUP COMPLETE ===\n";
echo "Fixed {$fixedCount} out of {$orphanedOperators->count()} orphaned operators\n\n";

// Verify the fix
$remainingOrphans = Operator::whereDoesntHave('poste')->count();
echo "Remaining orphaned operators: {$remainingOrphans}\n";

if ($remainingOrphans === 0) {
    echo "🎉 All operators now have valid poste assignments!\n";
} else {
    echo "⚠️  Some operators still have orphaned poste references\n";
}
