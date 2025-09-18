<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing POLY poste specifically:\n";

$polyPoste = App\Models\Poste::where('name', 'POLY')->first();
if ($polyPoste) {
    echo "POLY Poste found - ID: {$polyPoste->id}, Name: {$polyPoste->name}, Critical: " . ($polyPoste->is_critical ? 'Yes' : 'No') . "\n";
} else {
    echo "POLY poste not found\n";
}

$polyvalentPoste = App\Models\Poste::where('name', 'Polyvalent')->first();
if ($polyvalentPoste) {
    echo "Polyvalent Poste found - ID: {$polyvalentPoste->id}, Name: {$polyvalentPoste->name}, Critical: " . ($polyvalentPoste->is_critical ? 'Yes' : 'No') . "\n";
} else {
    echo "Polyvalent poste not found\n";
}

echo "\nOperator with POLY poste:\n";
$operatorWithPoly = App\Models\Operator::with('poste')->where('first_name', 'Khadija')->where('last_name', 'El Amrani')->first();
if ($operatorWithPoly) {
    echo "Operator: {$operatorWithPoly->first_name} {$operatorWithPoly->last_name}\n";
    echo "Poste ID: {$operatorWithPoly->poste_id}\n";
    if ($operatorWithPoly->poste) {
        echo "Poste Name: {$operatorWithPoly->poste->name}\n";
        echo "Poste Critical: " . ($operatorWithPoly->poste->is_critical ? 'Yes' : 'No') . "\n";
    } else {
        echo "No poste relationship found\n";
    }
}
