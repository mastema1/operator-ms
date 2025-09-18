<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing operator-poste relationships:\n";

// Test all operators to see if any have missing postes
$operators = App\Models\Operator::with('poste')->get();

$withPoste = 0;
$withoutPoste = 0;

foreach($operators as $op) {
    if ($op->poste) {
        $withPoste++;
        echo "✓ Operator: {$op->first_name} {$op->last_name} | Poste: {$op->poste->name} | poste_id: {$op->poste_id} | tenant_id: {$op->tenant_id}\n";
    } else {
        $withoutPoste++;
        echo "✗ Operator: {$op->first_name} {$op->last_name} | Poste: MISSING | poste_id: {$op->poste_id} | tenant_id: {$op->tenant_id}\n";
    }
}

echo "\nSummary:\n";
echo "Operators with postes: $withPoste\n";
echo "Operators without postes: $withoutPoste\n";

// Check if there are tenant mismatches
echo "\nChecking tenant relationships:\n";
$operatorsWithoutPostes = App\Models\Operator::whereDoesntHave('poste')->get();
foreach($operatorsWithoutPostes as $op) {
    $poste = App\Models\Poste::withoutGlobalScopes()->find($op->poste_id);
    if ($poste) {
        echo "Operator {$op->first_name} {$op->last_name} (tenant: {$op->tenant_id}) -> Poste {$poste->name} (tenant: {$poste->tenant_id})\n";
    }
}
