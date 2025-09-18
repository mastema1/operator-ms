<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Operator;
use App\Models\Poste;
use App\Models\CriticalPosition;
use Illuminate\Support\Facades\DB;

class RealOperatorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenantId = 1; // Seed for tenant 1
        
        echo "Clearing existing operator data for tenant {$tenantId}...\n";
        
        // Clear existing operator-related data for this tenant only (keep users)
        DB::table('attendances')->where('tenant_id', $tenantId)->delete();
        DB::table('backup_assignments')->where('tenant_id', $tenantId)->delete();
        DB::table('critical_positions')->where('tenant_id', $tenantId)->delete();
        DB::table('operators')->where('tenant_id', $tenantId)->delete();
        DB::table('postes')->where('tenant_id', $tenantId)->delete();
        
        echo "Seeding fresh operator data from real_operators.csv for tenant {$tenantId}...\n";
        
        // Read the CSV file
        $csvFile = database_path('seeders/real_operators.csv');
        
        if (!file_exists($csvFile)) {
            echo "Error: real_operators.csv not found at {$csvFile}\n";
            return;
        }
        
        $csvData = array_map('str_getcsv', file($csvFile));
        $header = array_shift($csvData); // Remove header row
        
        DB::beginTransaction();
        
        try {
            $createdCount = 0;
            $criticalPositionsCreated = 0;
            
            foreach ($csvData as $row) {
                $data = array_combine($header, $row);
                
                // Skip empty rows
                if (empty($data['matricule']) || empty($data['first_name'])) {
                    continue;
                }
                
                // Find or create the poste
                $poste = Poste::firstOrCreate(
                    [
                        'name' => $data['poste_name'],
                        'tenant_id' => $tenantId
                    ],
                    [
                        'ligne' => null // Postes don't have ligne anymore
                    ]
                );
                
                // Create the operator with Ligne 1 for all entries (fresh start)
                $operator = Operator::create([
                    'matricule' => $data['matricule'],
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'poste_id' => $poste->id,
                    'anciente' => $data['anciente'] ?: null,
                    'type_de_contrat' => $data['type_de_contrat'] ?: null,
                    'ligne' => 'Ligne 1', // Set all to Ligne 1 as requested
                    'is_capable' => filter_var($data['is_capable'], FILTER_VALIDATE_BOOLEAN),
                    'tenant_id' => $tenantId
                ]);
                
                $createdCount++;
                
                // Handle critical status in the new position-based system
                if (filter_var($data['is_critical'], FILTER_VALIDATE_BOOLEAN)) {
                    // Create or update critical position record for this poste+ligne combination
                    $criticalPosition = CriticalPosition::firstOrCreate(
                        [
                            'poste_id' => $poste->id,
                            'ligne' => 'Ligne 1',
                            'tenant_id' => $tenantId
                        ],
                        [
                            'is_critical' => true
                        ]
                    );
                    
                    // If it was just created, increment counter
                    if ($criticalPosition->wasRecentlyCreated) {
                        $criticalPositionsCreated++;
                    }
                }
                
                echo "Created operator: {$data['first_name']} {$data['last_name']} ({$data['poste_name']} - Ligne 1)" . 
                     (filter_var($data['is_critical'], FILTER_VALIDATE_BOOLEAN) ? " [CRITICAL]" : "") . "\n";
            }
            
            DB::commit();
            
            echo "\n✅ Seeding completed successfully!\n";
            echo "📊 Summary:\n";
            echo "   - Operators created: {$createdCount}\n";
            echo "   - Critical positions created: {$criticalPositionsCreated}\n";
            echo "   - All operators assigned to: Ligne 1\n";
            echo "   - Tenant ID: {$tenantId}\n";
            
        } catch (\Exception $e) {
            DB::rollBack();
            echo "\n❌ Error during seeding: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}
