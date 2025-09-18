<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use App\Http\Controllers\OperatorController;
use App\Http\Requests\StoreOperatorRequest;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Poste;
use App\Models\Operator;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== OPERATOR CREATION FLOW TEST ===\n\n";

// Get a test user with tenant
$user = User::with('tenant')->first();
if (!$user) {
    echo "❌ No users found in database\n";
    exit(1);
}

echo "✅ Test User: {$user->name} (ID: {$user->id})\n";
echo "✅ Tenant: {$user->tenant->name} (ID: {$user->tenant_id})\n\n";

// Authenticate the user
auth()->login($user);
echo "✅ User authenticated\n\n";

// Get available postes for this tenant
$postes = Poste::select('id', 'name', 'tenant_id')->get();
echo "📋 Available Postes for Tenant {$user->tenant_id}:\n";
foreach ($postes as $poste) {
    $marker = $poste->tenant_id == $user->tenant_id ? "✅" : "❌";
    echo "   {$marker} ID: {$poste->id}, Name: {$poste->name}, Tenant: {$poste->tenant_id}\n";
}

$tenantPostes = $postes->where('tenant_id', $user->tenant_id);
if ($tenantPostes->isEmpty()) {
    echo "\n❌ No postes found for current tenant\n";
    exit(1);
}

$testPoste = $tenantPostes->first();
echo "\n🎯 Using test poste: {$testPoste->name} (ID: {$testPoste->id})\n\n";

// Simulate form data
$formData = [
    'first_name' => 'Test',
    'last_name' => 'Operator',
    'matricule' => 'TEST-' . time(),
    'poste_id' => (string) $testPoste->id, // Form sends as string
    'is_capable' => '1',
    'is_critical' => '0',
    'anciente' => '2 years',
    'type_de_contrat' => 'CDI',
    'ligne' => 'Ligne 1'
];

echo "📝 Form Data to Submit:\n";
foreach ($formData as $key => $value) {
    echo "   {$key}: {$value} (" . gettype($value) . ")\n";
}
echo "\n";

// Test validation manually
echo "🔍 Testing Validation...\n";
$validator = \Validator::make($formData, [
    'matricule' => ['nullable','string','max:255','unique:operators,matricule'],
    'first_name' => ['required','string','max:255'],
    'last_name' => ['required','string','max:255'],
    'poste_id' => ['required','integer','exists:postes,id'],
    'is_capable' => ['boolean'],
    'anciente' => ['nullable','string','max:255'],
    'type_de_contrat' => ['nullable','string','in:ANAPEC,AWRACH,TES,CDI,CDD 6 mois,CDD 1 ans,CDD 2 ans,CDD 3 ans'],
    'ligne' => ['nullable','string','in:Ligne 1,Ligne 2,Ligne 3,Ligne 4,Ligne 5,Ligne 6,Ligne 7,Ligne 8,Ligne 9,Ligne 10'],
    'is_critical' => ['boolean'],
]);

// Prepare data like the request does
$preparedData = $formData;
$preparedData['poste_id'] = (int) $preparedData['poste_id'];
$preparedData['is_capable'] = $preparedData['is_capable'] === '1';
$preparedData['is_critical'] = $preparedData['is_critical'] === '1';

$validator = \Validator::make($preparedData, [
    'matricule' => ['nullable','string','max:255','unique:operators,matricule'],
    'first_name' => ['required','string','max:255'],
    'last_name' => ['required','string','max:255'],
    'poste_id' => ['required','integer','exists:postes,id'],
    'is_capable' => ['boolean'],
    'anciente' => ['nullable','string','max:255'],
    'type_de_contrat' => ['nullable','string','in:ANAPEC,AWRACH,TES,CDI,CDD 6 mois,CDD 1 ans,CDD 2 ans,CDD 3 ans'],
    'ligne' => ['nullable','string','in:Ligne 1,Ligne 2,Ligne 3,Ligne 4,Ligne 5,Ligne 6,Ligne 7,Ligne 8,Ligne 9,Ligne 10'],
    'is_critical' => ['boolean'],
]);

if ($validator->fails()) {
    echo "❌ Validation failed:\n";
    foreach ($validator->errors()->all() as $error) {
        echo "   - {$error}\n";
    }
    exit(1);
}

$validatedData = $validator->validated();
echo "✅ Validation passed\n";
echo "📋 Validated Data:\n";
foreach ($validatedData as $key => $value) {
    echo "   {$key}: " . (is_bool($value) ? ($value ? 'true' : 'false') : $value) . " (" . gettype($value) . ")\n";
}
echo "\n";

// Test operator creation
echo "👤 Creating Operator...\n";
try {
    // Add tenant_id manually (simulating controller logic)
    $validatedData['tenant_id'] = $user->tenant_id;
    
    $operator = Operator::create($validatedData);
    echo "✅ Operator created with ID: {$operator->id}\n";
    
    // Load the poste relationship
    $operator->load('poste');
    
    echo "📋 Created Operator Details:\n";
    echo "   ID: {$operator->id}\n";
    echo "   Name: {$operator->first_name} {$operator->last_name}\n";
    echo "   Poste ID: {$operator->poste_id}\n";
    echo "   Poste Name: " . ($operator->poste?->name ?? 'NULL') . "\n";
    echo "   Tenant ID: {$operator->tenant_id}\n";
    echo "   Created At: {$operator->created_at}\n";
    
} catch (Exception $e) {
    echo "❌ Operator creation failed: " . $e->getMessage() . "\n";
    exit(1);
}
echo "\n";

// Verify the operator can be retrieved with poste
echo "🔍 Verifying Operator Retrieval...\n";
$retrievedOperator = Operator::with('poste')->find($operator->id);
if ($retrievedOperator && $retrievedOperator->poste) {
    echo "✅ Operator retrieved successfully with poste: {$retrievedOperator->poste->name}\n";
} else {
    echo "❌ Failed to retrieve operator with poste relationship\n";
    if ($retrievedOperator) {
        echo "   Operator exists but poste is: " . ($retrievedOperator->poste ? 'loaded' : 'NULL') . "\n";
        echo "   Raw poste_id: {$retrievedOperator->poste_id}\n";
    }
}

// Test tenant scoping
echo "\n🔒 Testing Tenant Scoping...\n";
$allOperatorsForTenant = Operator::with('poste')->get();
echo "   Found {$allOperatorsForTenant->count()} operators for current tenant\n";

$operatorsWithoutPostes = $allOperatorsForTenant->filter(function($op) {
    return !$op->poste;
});
echo "   Operators without postes: {$operatorsWithoutPostes->count()}\n";

if ($operatorsWithoutPostes->count() > 0) {
    echo "   ❌ Found operators without postes:\n";
    foreach ($operatorsWithoutPostes as $op) {
        echo "      ID: {$op->id}, Name: {$op->first_name} {$op->last_name}, Poste ID: {$op->poste_id}\n";
    }
} else {
    echo "   ✅ All operators have postes assigned\n";
}

echo "\n=== TEST COMPLETED ===\n";
