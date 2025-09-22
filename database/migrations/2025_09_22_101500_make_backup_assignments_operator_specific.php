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
        Schema::table('backup_assignments', function (Blueprint $table) {
            // Add operator_id to make backup assignments operator-specific
            $table->foreignId('operator_id')->after('poste_id')->constrained()->onDelete('cascade');
            
            // Drop the old unique constraint that was position-based
            $table->dropUnique('unique_poste_backup_slot_date');
            
            // Add new unique constraint that is operator-specific
            $table->unique(['operator_id', 'assigned_date'], 'unique_operator_backup_date');
            
            // Add index for efficient queries
            $table->index(['operator_id', 'assigned_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('backup_assignments', function (Blueprint $table) {
            // Drop the new constraints and indexes
            $table->dropUnique('unique_operator_backup_date');
            $table->dropIndex(['operator_id', 'assigned_date']);
            
            // Remove the operator_id column
            $table->dropForeign(['operator_id']);
            $table->dropColumn('operator_id');
            
            // Restore the old unique constraint
            $table->unique(['poste_id', 'backup_slot', 'assigned_date'], 'unique_poste_backup_slot_date');
        });
    }
};
