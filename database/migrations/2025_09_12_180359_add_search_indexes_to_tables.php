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
        // Add individual indexes instead of composite to avoid key length issues
        DB::statement('CREATE INDEX idx_operators_first_name ON operators (first_name(50))');
        DB::statement('CREATE INDEX idx_operators_last_name ON operators (last_name(50))');
        
        Schema::table('backup_assignments', function (Blueprint $table) {
            // Add composite index for backup assignment lookups
            $table->index(['assigned_date', 'poste_id'], 'idx_backup_assignments_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX idx_operators_first_name ON operators');
        DB::statement('DROP INDEX idx_operators_last_name ON operators');
        
        Schema::table('backup_assignments', function (Blueprint $table) {
            $table->dropIndex('idx_backup_assignments_lookup');
        });
    }
};
