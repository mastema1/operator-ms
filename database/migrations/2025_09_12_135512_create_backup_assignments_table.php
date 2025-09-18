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
        Schema::create('backup_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poste_id')->constrained()->onDelete('cascade');
            $table->foreignId('backup_operator_id')->constrained('operators')->onDelete('cascade');
            $table->tinyInteger('backup_slot')->comment('1 for first backup, 2 for second backup');
            $table->date('assigned_date');
            $table->timestamps();
            
            $table->unique(['poste_id', 'backup_slot', 'assigned_date'], 'unique_poste_backup_slot_date');
            $table->index(['poste_id', 'assigned_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_assignments');
    }
};
