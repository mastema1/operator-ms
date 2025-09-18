<?php

namespace Database\Seeders;

use App\Models\Operator;
use App\Models\Poste;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class CsvOperatorSeeder extends Seeder
{
    public function run(): void
    {
        $csvFile = 'real_operators.csv';
        $csvPath = database_path('seeders/' . $csvFile);
        
        if (!file_exists($csvPath)) {
            $this->command->error("CSV file not found at: {$csvPath}");
            $this->command->info("Please create the file with the following structure:");
            $this->command->info("matricule,first_name,last_name,poste_name,anciente,type_de_contrat,ligne,is_capable,is_critical");
            return;
        }

        // Clear existing operators
        Operator::truncate();
        
        // Get all tenants to create operators for each
        $tenants = Tenant::all();
        
        $handle = fopen($csvPath, 'r');
        $header = fgetcsv($handle); // Skip header row
        
        $imported = 0;
        $errors = 0;
        
        while (($data = fgetcsv($handle)) !== false) {
            try {
                // Map CSV columns to array
                $operatorData = array_combine($header, $data);
                
                // Create operators for each tenant
                foreach ($tenants as $tenant) {
                    // Find or create poste for this tenant
                    $poste = Poste::where('tenant_id', $tenant->id)
                        ->where('name', $operatorData['poste_name'])
                        ->first();
                    
                    if (!$poste) {
                        $poste = Poste::create([
                            'name' => $operatorData['poste_name'],
                            'tenant_id' => $tenant->id,
                        ]);
                    }
                    
                    // Create operator for this tenant
                    $operator = Operator::create([
                        'matricule' => $operatorData['matricule'] . '-T' . $tenant->id,
                        'first_name' => $operatorData['first_name'],
                        'last_name' => $operatorData['last_name'],
                        'poste_id' => $poste->id,
                        'anciente' => $operatorData['anciente'] ?? '1 ans',
                        'type_de_contrat' => $operatorData['type_de_contrat'] ?? 'CDI',
                        'ligne' => 'Ligne 1', // Force all entries to Ligne 1
                        'is_capable' => filter_var($operatorData['is_capable'] ?? true, FILTER_VALIDATE_BOOLEAN),
                        'tenant_id' => $tenant->id,
                    ]);

                    // Handle critical status using the new critical_positions system
                    $isCritical = filter_var($operatorData['is_critical'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    if ($isCritical) {
                        // Create or update critical position record for this poste+ligne combination
                        \App\Models\CriticalPosition::updateOrCreate([
                            'poste_id' => $poste->id,
                            'ligne' => 'Ligne 1',
                            'tenant_id' => $tenant->id,
                        ], [
                            'is_critical' => true,
                        ]);
                    }
                }
                
                $imported++;
                
            } catch (\Exception $e) {
                $errors++;
                $this->command->error("Error importing row: " . implode(',', $data));
                $this->command->error("Error: " . $e->getMessage());
            }
        }
        
        fclose($handle);
        
        $this->command->info("CSV Import completed:");
        $this->command->info("- Imported: {$imported} operators");
        if ($errors > 0) {
            $this->command->warn("- Errors: {$errors} rows failed");
        }
    }
}
