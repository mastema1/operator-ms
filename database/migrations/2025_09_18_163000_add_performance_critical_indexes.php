<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add composite index for critical positions queries (tenant_id, is_critical)
        Schema::table('critical_positions', function (Blueprint $table) {
            $table->index(['tenant_id', 'is_critical'], 'idx_critical_positions_tenant_critical');
            $table->index(['poste_id', 'ligne'], 'idx_critical_positions_poste_ligne');
        });

        // Add composite index for attendance queries (date, status)
        Schema::table('attendances', function (Blueprint $table) {
            $table->index(['date', 'status'], 'idx_attendances_date_status');
            $table->index(['tenant_id', 'date'], 'idx_attendances_tenant_date');
        });

        // Add index for backup assignments date queries
        Schema::table('backup_assignments', function (Blueprint $table) {
            $table->index('assigned_date', 'idx_backup_assignments_date');
            $table->index(['tenant_id', 'assigned_date'], 'idx_backup_assignments_tenant_date');
        });

        // Add composite index for operators critical position lookups
        Schema::table('operators', function (Blueprint $table) {
            $table->index(['poste_id', 'ligne'], 'idx_operators_poste_ligne');
            $table->index(['tenant_id', 'poste_id'], 'idx_operators_tenant_poste');
        });

        // Add index for postes name searches (if not already exists)
        Schema::table('postes', function (Blueprint $table) {
            $table->index(['tenant_id', 'name'], 'idx_postes_tenant_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('critical_positions', function (Blueprint $table) {
            $table->dropIndex('idx_critical_positions_tenant_critical');
            $table->dropIndex('idx_critical_positions_poste_ligne');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('idx_attendances_date_status');
            $table->dropIndex('idx_attendances_tenant_date');
        });

        Schema::table('backup_assignments', function (Blueprint $table) {
            $table->dropIndex('idx_backup_assignments_date');
            $table->dropIndex('idx_backup_assignments_tenant_date');
        });

        Schema::table('operators', function (Blueprint $table) {
            $table->dropIndex('idx_operators_poste_ligne');
            $table->dropIndex('idx_operators_tenant_poste');
        });

        Schema::table('postes', function (Blueprint $table) {
            $table->dropIndex('idx_postes_tenant_name');
        });
    }
};
