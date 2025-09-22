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
        // Clean up duplicate numbered postes and standardize to zero-padded format
        
        // Get all tenants
        $tenants = DB::table('tenants')->get();
        
        foreach ($tenants as $tenant) {
            // For each number 1-40, ensure we only have the zero-padded version
            for ($i = 1; $i <= 40; $i++) {
                $paddedName = 'Poste ' . str_pad($i, 2, '0', STR_PAD_LEFT);
                $unpaddedName = 'Poste ' . $i;
                
                // Check if both versions exist
                $paddedExists = DB::table('postes')
                    ->where('tenant_id', $tenant->id)
                    ->where('name', $paddedName)
                    ->exists();
                    
                $unpaddedExists = DB::table('postes')
                    ->where('tenant_id', $tenant->id)
                    ->where('name', $unpaddedName)
                    ->exists();
                
                if ($paddedExists && $unpaddedExists) {
                    // Both exist - keep the zero-padded version, delete the unpadded
                    
                    // First, update any operators that reference the unpadded version
                    $unpaddedPoste = DB::table('postes')
                        ->where('tenant_id', $tenant->id)
                        ->where('name', $unpaddedName)
                        ->first();
                        
                    $paddedPoste = DB::table('postes')
                        ->where('tenant_id', $tenant->id)
                        ->where('name', $paddedName)
                        ->first();
                    
                    if ($unpaddedPoste && $paddedPoste) {
                        // Update operators to reference the padded version
                        DB::table('operators')
                            ->where('poste_id', $unpaddedPoste->id)
                            ->update(['poste_id' => $paddedPoste->id]);
                        
                        // Update critical_positions to reference the padded version
                        DB::table('critical_positions')
                            ->where('poste_id', $unpaddedPoste->id)
                            ->update(['poste_id' => $paddedPoste->id]);
                        
                        // Update backup_assignments to reference the padded version
                        DB::table('backup_assignments')
                            ->where('poste_id', $unpaddedPoste->id)
                            ->update(['poste_id' => $paddedPoste->id]);
                        
                        // Delete the unpadded version
                        DB::table('postes')
                            ->where('id', $unpaddedPoste->id)
                            ->delete();
                    }
                } elseif ($unpaddedExists && !$paddedExists) {
                    // Only unpadded exists - rename it to padded format
                    DB::table('postes')
                        ->where('tenant_id', $tenant->id)
                        ->where('name', $unpaddedName)
                        ->update(['name' => $paddedName]);
                }
                // If only padded exists, or neither exists, no action needed
            }
        }
        
        // Clear any caches that might contain the old poste names
        if (function_exists('cache')) {
            cache()->flush();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
