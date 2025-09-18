<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\SqlInjectionSeeder;

class ImportSqlData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'db:import-sql 
                            {--fresh : Drop all tables and recreate them before importing}
                            {--confirm : Skip confirmation prompts}';

    /**
     * The console command description.
     */
    protected $description = 'Import data from sql_injections.sql file';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('SQL Data Import Tool');
        $this->info('===================');
        
        // Check if SQL file exists
        $sqlFilePath = base_path('sql_injections.sql');
        if (!file_exists($sqlFilePath)) {
            $this->error("SQL injection file not found at: {$sqlFilePath}");
            $this->info("Please ensure the sql_injections.sql file exists in the project root.");
            return 1;
        }
        
        // Show file info
        $fileSize = number_format(filesize($sqlFilePath));
        $this->info("Found SQL file: {$fileSize} bytes");
        
        // Fresh migration option
        if ($this->option('fresh')) {
            if (!$this->option('confirm')) {
                if (!$this->confirm('This will drop all tables and recreate them. Are you sure?')) {
                    $this->info('Operation cancelled.');
                    return 0;
                }
            }
            
            $this->info('Running fresh migrations...');
            $this->call('migrate:fresh');
        }
        
        // Confirmation for data import
        if (!$this->option('confirm')) {
            if (!$this->confirm('This will import data into your database. Continue?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }
        
        // Run the SQL injection seeder
        $this->info('Starting data import...');
        $seeder = new SqlInjectionSeeder();
        $seeder->setCommand($this);
        $seeder->run();
        
        $this->info('Data import completed successfully!');
        return 0;
    }
}
