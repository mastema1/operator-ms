<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add only missing critical indexes for concurrent load performance
        
        // Add missing indexes for operators table
        Schema::table('operators', function (Blueprint $table) {
            // Check if indexes don't exist before creating
            if (!$this->indexExists('operators', 'idx_operators_tenant_firstname')) {
                $table->index(['tenant_id', 'first_name'], 'idx_operators_tenant_firstname');
            }
            if (!$this->indexExists('operators', 'idx_operators_tenant_lastname')) {
                $table->index(['tenant_id', 'last_name'], 'idx_operators_tenant_lastname');
            }
            if (!$this->indexExists('operators', 'idx_operators_tenant_matricule')) {
                $table->index(['tenant_id', 'matricule'], 'idx_operators_tenant_matricule');
            }
        });

        // Add missing indexes for attendances table
        Schema::table('attendances', function (Blueprint $table) {
            // Add composite index for tenant + operator + date (critical for write performance)
            if (!$this->indexExists('attendances', 'idx_attendances_tenant_operator_date')) {
                $table->index(['tenant_id', 'operator_id', 'date'], 'idx_attendances_tenant_operator_date');
            }
            
            // Add index for status-based queries
            if (!$this->indexExists('attendances', 'idx_attendances_tenant_status')) {
                $table->index(['tenant_id', 'status'], 'idx_attendances_tenant_status');
            }
        });

        // Add missing indexes for critical_positions table
        Schema::table('critical_positions', function (Blueprint $table) {
            // Add comprehensive dashboard query index
            if (!$this->indexExists('critical_positions', 'idx_critical_positions_dashboard')) {
                $table->index(['tenant_id', 'poste_id', 'ligne', 'is_critical'], 'idx_critical_positions_dashboard');
            }
        });

        // Add missing indexes for backup_assignments table
        Schema::table('backup_assignments', function (Blueprint $table) {
            // Add comprehensive dashboard backup index
            if (!$this->indexExists('backup_assignments', 'idx_backup_assignments_dashboard')) {
                $table->index(['tenant_id', 'poste_id', 'assigned_date'], 'idx_backup_assignments_dashboard');
            }
            
            // Add index for backup operator lookups
            if (!$this->indexExists('backup_assignments', 'idx_backup_assignments_operator')) {
                $table->index(['backup_operator_id', 'assigned_date'], 'idx_backup_assignments_operator');
            }
        });

        // Add missing indexes for users table
        Schema::table('users', function (Blueprint $table) {
            // Add tenant index if it doesn't exist
            if (!$this->indexExists('users', 'idx_users_tenant')) {
                $table->index(['tenant_id'], 'idx_users_tenant');
            }
        });
    }
    
    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $indexes = \DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
            return count($indexes) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop only the indexes we created
        Schema::table('operators', function (Blueprint $table) {
            if ($this->indexExists('operators', 'idx_operators_tenant_firstname')) {
                $table->dropIndex('idx_operators_tenant_firstname');
            }
            if ($this->indexExists('operators', 'idx_operators_tenant_lastname')) {
                $table->dropIndex('idx_operators_tenant_lastname');
            }
            if ($this->indexExists('operators', 'idx_operators_tenant_matricule')) {
                $table->dropIndex('idx_operators_tenant_matricule');
            }
        });

        Schema::table('attendances', function (Blueprint $table) {
            if ($this->indexExists('attendances', 'idx_attendances_tenant_operator_date')) {
                $table->dropIndex('idx_attendances_tenant_operator_date');
            }
            if ($this->indexExists('attendances', 'idx_attendances_tenant_status')) {
                $table->dropIndex('idx_attendances_tenant_status');
            }
        });

        Schema::table('critical_positions', function (Blueprint $table) {
            if ($this->indexExists('critical_positions', 'idx_critical_positions_dashboard')) {
                $table->dropIndex('idx_critical_positions_dashboard');
            }
        });

        Schema::table('backup_assignments', function (Blueprint $table) {
            if ($this->indexExists('backup_assignments', 'idx_backup_assignments_dashboard')) {
                $table->dropIndex('idx_backup_assignments_dashboard');
            }
            if ($this->indexExists('backup_assignments', 'idx_backup_assignments_operator')) {
                $table->dropIndex('idx_backup_assignments_operator');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if ($this->indexExists('users', 'idx_users_tenant')) {
                $table->dropIndex('idx_users_tenant');
            }
        });
    }
};
