<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operators', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->foreignId('poste_id')->constrained('postes')->cascadeOnDelete();
            $table->boolean('is_capable')->default(true);
            $table->string('matricule')->nullable()->unique();
            $table->string('anciente')->nullable();
            $table->string('type_de_contrat')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operators');
    }
}; 