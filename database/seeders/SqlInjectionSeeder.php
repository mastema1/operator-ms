<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SqlInjectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sqlFilePath = base_path('sql_injections.sql');
        
        // Check if the SQL file exists
        if (!File::exists($sqlFilePath)) {
            $this->command->error("SQL injection file not found at: {$sqlFilePath}");
            $this->command->info("Please ensure the sql_injections.sql file exists in the project root.");
            return;
        }
        
        $this->command->info("Reading SQL injection file...");
        
        // Read the SQL file content
        $sqlContent = File::get($sqlFilePath);
        
        if (empty($sqlContent)) {
            $this->command->error("SQL injection file is empty or could not be read.");
            return;
        }
        
        $this->command->info("Executing SQL statements...");
        
        // Split the SQL content into individual statements
        // Remove comments and empty lines
        $statements = $this->parseSqlStatements($sqlContent);
        
        $executedCount = 0;
        $errorCount = 0;
        
        // Execute each statement
        foreach ($statements as $statement) {
            try {
                if (!empty(trim($statement))) {
                    DB::unprepared($statement);
                    $executedCount++;
                }
            } catch (\Exception $e) {
                $errorCount++;
                $this->command->warn("Error executing statement: " . substr($statement, 0, 100) . "...");
                $this->command->warn("Error: " . $e->getMessage());
            }
        }
        
        $this->command->info("SQL injection completed!");
        $this->command->info("- Statements executed: {$executedCount}");
        
        if ($errorCount > 0) {
            $this->command->warn("- Statements with errors: {$errorCount}");
        }
        
        // Display summary of imported data
        $this->displayDataSummary();
    }
    
    /**
     * Parse SQL content into individual statements
     */
    private function parseSqlStatements(string $sqlContent): array
    {
        // Split by semicolon and newline
        $lines = explode("\n", $sqlContent);
        $statements = [];
        $currentStatement = '';
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip comments and empty lines
            if (empty($line) || str_starts_with($line, '--')) {
                continue;
            }
            
            $currentStatement .= $line . ' ';
            
            // If line ends with semicolon, it's the end of a statement
            if (str_ends_with($line, ';')) {
                $statements[] = trim($currentStatement);
                $currentStatement = '';
            }
        }
        
        // Add any remaining statement
        if (!empty(trim($currentStatement))) {
            $statements[] = trim($currentStatement);
        }
        
        return $statements;
    }
    
    /**
     * Display summary of imported data
     */
    private function displayDataSummary(): void
    {
        $this->command->info("\nData import summary:");
        
        try {
            $tables = [
                'tenants' => \App\Models\Tenant::class,
                'users' => \App\Models\User::class,
                'postes' => \App\Models\Poste::class,
                'operators' => \App\Models\Operator::class,
                'critical_positions' => \App\Models\CriticalPosition::class,
                'attendances' => \App\Models\Attendance::class,
                'backup_assignments' => \App\Models\BackupAssignment::class,
            ];
            
            foreach ($tables as $tableName => $modelClass) {
                if (class_exists($modelClass)) {
                    $count = $modelClass::count();
                    $this->command->info("- " . ucfirst(str_replace('_', ' ', $tableName)) . ": {$count} records");
                }
            }
            
        } catch (\Exception $e) {
            $this->command->warn("Could not display data summary: " . $e->getMessage());
        }
    }
}
