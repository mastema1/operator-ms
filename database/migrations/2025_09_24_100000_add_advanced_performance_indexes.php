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
        // Advanced composite indexes for high-performance multi-tenant queries
        
        // Operators table - Critical for dashboard and operator listing performance
        Schema::table('operators', function (Blueprint $table) {
            // Composite index for tenant + poste + ligne (critical for dashboard queries)
            if (!$this->indexExists('operators', 'idx_operators_tenant_poste_ligne')) {
                $table->index(['tenant_id', 'poste_id', 'ligne'], 'idx_operators_tenant_poste_ligne');
            }
            
            // Separate indexes for search queries (avoid MySQL key length limit)
            if (!$this->indexExists('operators', 'idx_operators_tenant_firstname')) {
                DB::statement('CREATE INDEX idx_operators_tenant_firstname ON operators (tenant_id, first_name(50))');
            }
            
            if (!$this->indexExists('operators', 'idx_operators_tenant_lastname')) {
                DB::statement('CREATE INDEX idx_operators_tenant_lastname ON operators (tenant_id, last_name(50))');
            }
            
            // Index for matricule-based lookups
            if (!$this->indexExists('operators', 'idx_operators_tenant_matricule')) {
                DB::statement('CREATE INDEX idx_operators_tenant_matricule ON operators (tenant_id, matricule(20))');
            }
        });

        // Attendances table - Critical for real-time status queries
        Schema::table('attendances', function (Blueprint $table) {
            // Composite index for today's attendance queries (most frequent)
            if (!$this->indexExists('attendances', 'idx_attendances_today_lookup')) {
                $table->index(['tenant_id', 'date', 'operator_id', 'status'], 'idx_attendances_today_lookup');
            }
            
            // Index for status-based filtering
            if (!$this->indexExists('attendances', 'idx_attendances_tenant_status_date')) {
                $table->index(['tenant_id', 'status', 'date'], 'idx_attendances_tenant_status_date');
            }
        });

        // Critical positions table - Essential for dashboard performance
        Schema::table('critical_positions', function (Blueprint $table) {
            // Comprehensive index for all dashboard queries
            if (!$this->indexExists('critical_positions', 'idx_critical_positions_complete')) {
                $table->index(['tenant_id', 'is_critical', 'poste_id', 'ligne'], 'idx_critical_positions_complete');
            }
        });

        // Backup assignments table - For backup-related queries
        Schema::table('backup_assignments', function (Blueprint $table) {
            // Index for operator-specific backup lookups (new system)
            if (!$this->indexExists('backup_assignments', 'idx_backup_assignments_operator_date')) {
                $table->index(['tenant_id', 'operator_id', 'assigned_date'], 'idx_backup_assignments_operator_date');
            }
            
            // Index for backup operator availability queries
            if (!$this->indexExists('backup_assignments', 'idx_backup_assignments_backup_operator')) {
                $table->index(['backup_operator_id', 'assigned_date'], 'idx_backup_assignments_backup_operator');
            }
        });

        // Postes table - For poste-related queries
        Schema::table('postes', function (Blueprint $table) {
            // Composite index for tenant + name searches
            if (!$this->indexExists('postes', 'idx_postes_tenant_name')) {
                $table->index(['tenant_id', 'name'], 'idx_postes_tenant_name');
            }
        });

        // Users table - For authentication and tenant lookups
        Schema::table('users', function (Blueprint $table) {
            // Index for email + tenant combination
            if (!$this->indexExists('users', 'idx_users_email_tenant')) {
                $table->index(['email', 'tenant_id'], 'idx_users_email_tenant');
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
        // Drop the indexes we created
        Schema::table('operators', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_operators_tenant_poste_ligne');
            if ($this->indexExists('operators', 'idx_operators_tenant_firstname')) {
                DB::statement('DROP INDEX idx_operators_tenant_firstname ON operators');
            }
            if ($this->indexExists('operators', 'idx_operators_tenant_lastname')) {
                DB::statement('DROP INDEX idx_operators_tenant_lastname ON operators');
            }
            if ($this->indexExists('operators', 'idx_operators_tenant_matricule')) {
                DB::statement('DROP INDEX idx_operators_tenant_matricule ON operators');
            }
        });

        Schema::table('attendances', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_attendances_today_lookup');
            $this->dropIndexIfExists($table, 'idx_attendances_tenant_status_date');
        });

        Schema::table('critical_positions', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_critical_positions_complete');
        });

        Schema::table('backup_assignments', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_backup_assignments_operator_date');
            $this->dropIndexIfExists($table, 'idx_backup_assignments_backup_operator');
        });

        Schema::table('postes', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_postes_tenant_name');
        });

        Schema::table('users', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_users_email_tenant');
        });
    }

    private function dropIndexIfExists($table, string $indexName): void
    {
        if ($this->indexExists($table->getTable(), $indexName)) {
            $table->dropIndex($indexName);
        }
    }
};
