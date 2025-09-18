<?php

require_once 'vendor/autoload.php';

use App\Models\Poste;
use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING CRITICAL POSTES STATUS ===\n\n";

// Get a test user with tenant
$user = User::with('tenant')->first();
if (!$user) {
    echo "❌ No users found in database\n";
    exit(1);
}

// Authenticate the user
auth()->login($user);

echo "✅ Test User: {$user->name} (Tenant: {$user->tenant->name})\n\n";

// Check ABS and Bol postes
$abs = Poste::where('name', 'ABS')->first();
$bol = Poste::where('name', 'Bol')->first();

echo "🔍 CURRENT STATUS:\n";
echo "==================\n";

if ($abs) {
    $absStatus = $abs->is_critical ? 'CRITICAL' : 'NON-CRITICAL';
    echo "ABS: {$absStatus} (ID: {$abs->id}, Tenant: {$abs->tenant_id})\n";
} else {
    echo "ABS: NOT FOUND\n";
}

if ($bol) {
    $bolStatus = $bol->is_critical ? 'CRITICAL' : 'NON-CRITICAL';
    echo "Bol: {$bolStatus} (ID: {$bol->id}, Tenant: {$bol->tenant_id})\n";
} else {
    echo "Bol: NOT FOUND\n";
}

// Check all critical postes
echo "\n🔍 ALL CRITICAL POSTES:\n";
echo "======================\n";

$criticalPostes = Poste::where('is_critical', true)->get();
echo "Found {$criticalPostes->count()} critical postes:\n";

foreach ($criticalPostes as $poste) {
    echo "   ✅ {$poste->name} (ID: {$poste->id}, Tenant: {$poste->tenant_id})\n";
}

if ($criticalPostes->count() == 0) {
    echo "   ℹ️  No critical postes found\n";
}

// Fix the issue by setting ABS and Bol to non-critical
echo "\n🔧 FIXING ABS AND BOL STATUS:\n";
echo "=============================\n";

if ($abs && $abs->is_critical) {
    $abs->update(['is_critical' => false]);
    echo "✅ ABS set to NON-CRITICAL\n";
} else {
    echo "ℹ️  ABS already NON-CRITICAL or not found\n";
}

if ($bol && $bol->is_critical) {
    $bol->update(['is_critical' => false]);
    echo "✅ Bol set to NON-CRITICAL\n";
} else {
    echo "ℹ️  Bol already NON-CRITICAL or not found\n";
}

// Verify the fix
echo "\n✅ VERIFICATION:\n";
echo "================\n";

$abs = Poste::where('name', 'ABS')->first();
$bol = Poste::where('name', 'Bol')->first();

if ($abs) {
    $absStatus = $abs->is_critical ? 'CRITICAL' : 'NON-CRITICAL';
    echo "ABS: {$absStatus}\n";
}

if ($bol) {
    $bolStatus = $bol->is_critical ? 'CRITICAL' : 'NON-CRITICAL';
    echo "Bol: {$bolStatus}\n";
}

echo "\n=== COMPLETED ===\n";
