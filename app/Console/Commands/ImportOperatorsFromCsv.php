<?php

namespace App\Console\Commands;

use App\Models\Operator;
use App\Models\Poste;
use Illuminate\Console\Command;

class ImportOperatorsFromCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'operators:import {file?} {--clear}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import operators from CSV file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $csvFile = $this->argument('file') ?? 'operators.csv';
        $csvPath = database_path('seeders/' . $csvFile);
        
        if (!file_exists($csvPath)) {
            $this->error("CSV file not found at: {$csvPath}");
            $this->info("Please create the file with the following structure:");
            $this->info("matricule,first_name,last_name,poste_name,anciente,type_de_contrat,ligne,is_capable,is_critical");
            return 1;
        }

        // Clear existing operators if requested
        if ($this->option('clear')) {
            if ($this->confirm('This will delete all existing operators. Continue?')) {
                Operator::truncate();
                $this->info('Existing operators cleared.');
            } else {
                return 0;
            }
        }
        
        $handle = fopen($csvPath, 'r');
        $header = fgetcsv($handle); // Skip header row
        
        $imported = 0;
        $errors = 0;
        
        $this->info("Starting CSV import from: {$csvFile}");
        
        while (($data = fgetcsv($handle)) !== false) {
            try {
                // Map CSV columns to array
                $operatorData = array_combine($header, $data);
                
                // Find or create poste
                $poste = Poste::firstOrCreate(
                    ['name' => $operatorData['poste_name']],
                    ['is_critical' => filter_var($operatorData['is_critical'] ?? false, FILTER_VALIDATE_BOOLEAN)]
                );
                
                // Create operator
                Operator::create([
                    'matricule' => $operatorData['matricule'],
                    'first_name' => $operatorData['first_name'],
                    'last_name' => $operatorData['last_name'],
                    'poste_id' => $poste->id,
                    'anciente' => $operatorData['anciente'] ?? '1 ans',
                    'type_de_contrat' => $operatorData['type_de_contrat'] ?? 'CDI',
                    'ligne' => $operatorData['ligne'] ?? 'Ligne 1',
                    'is_capable' => filter_var($operatorData['is_capable'] ?? true, FILTER_VALIDATE_BOOLEAN),
                    'is_critical' => filter_var($operatorData['is_critical'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ]);
                
                $imported++;
                $this->info("✓ Imported: {$operatorData['first_name']} {$operatorData['last_name']} ({$operatorData['matricule']})");
                
            } catch (\Exception $e) {
                $errors++;
                $this->error("✗ Error importing row: " . implode(',', $data));
                $this->error("  Error: " . $e->getMessage());
            }
        }
        
        fclose($handle);
        
        $this->info("\n=== CSV Import Summary ===");
        $this->info("✓ Successfully imported: {$imported} operators");
        if ($errors > 0) {
            $this->warn("✗ Failed imports: {$errors} rows");
        }
        
        return 0;
    }
}
