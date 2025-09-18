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
        Schema::create('critical_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poste_id')->constrained()->onDelete('cascade');
            $table->string('ligne');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->boolean('is_critical')->default(false);
            $table->timestamps();
            
            // Ensure unique combination of poste_id + ligne + tenant_id
            $table->unique(['poste_id', 'ligne', 'tenant_id'], 'unique_poste_ligne_tenant');
            
            // Add indexes for performance
            $table->index(['tenant_id', 'is_critical']);
            $table->index(['poste_id', 'ligne']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('critical_positions');
    }
};
