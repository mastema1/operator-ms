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
        Schema::table('attendances', function (Blueprint $table) {
            $table->index('date', 'idx_attendances_date');
            $table->index(['operator_id', 'date'], 'idx_attendances_operator_date');
            $table->index('status', 'idx_attendances_status');
        });

        Schema::table('operators', function (Blueprint $table) {
            $table->index('poste_id', 'idx_operators_poste');
            $table->index('ligne', 'idx_operators_ligne');
        });

        Schema::table('postes', function (Blueprint $table) {
            $table->index('is_critical', 'idx_postes_critical');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            //
        });
    }
};
