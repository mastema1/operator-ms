<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Debug Routes (Development Only)
|--------------------------------------------------------------------------
|
| These routes are only available in development environment.
| They provide debugging and testing capabilities.
|
*/

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Cache management debug routes
    Route::get('/debug/cache-status', function() {
        return response()->json(\App\Services\DashboardCacheManager::getCacheStats());
    })->name('debug.cache-status');
    
    Route::post('/debug/clear-cache', function() {
        \App\Services\DashboardCacheManager::clearDashboardCache();
        return response()->json(['message' => 'Dashboard cache cleared successfully']);
    })->name('debug.clear-cache');
    
    // Poste sorting debug route
    Route::get('/debug/poste-sorting', function() {
        $postes = \App\Models\Poste::where('tenant_id', auth()->user()->tenant_id)->get();
        $sortedPostes = \App\Services\PosteSortingService::sortPostes($postes);
        $debugInfo = \App\Services\PosteSortingService::debugSorting($postes);
        
        return response()->json([
            'original_count' => $postes->count(),
            'sorted_count' => $sortedPostes->count(),
            'sorted_order' => $sortedPostes->pluck('name')->toArray(),
            'debug_info' => $debugInfo
        ]);
    })->name('debug.poste-sorting');
    
    // Poste duplicates analysis debug route
    Route::get('/debug/poste-duplicates', function() {
        $tenantId = auth()->user()->tenant_id;
        
        // Get all numbered postes for current tenant
        $numberedPostes = \App\Models\Poste::where('tenant_id', $tenantId)
            ->where('name', 'REGEXP', '^Poste [0-9]+$')
            ->orderBy('name')
            ->get();
            
        // Group by number to find duplicates
        $grouped = [];
        foreach ($numberedPostes as $poste) {
            if (preg_match('/^Poste (\d+)$/', $poste->name, $matches)) {
                $number = (int)$matches[1];
                if (!isset($grouped[$number])) {
                    $grouped[$number] = [];
                }
                $grouped[$number][] = $poste->name;
            }
        }
        
        $duplicates = [];
        foreach ($grouped as $number => $names) {
            if (count($names) > 1) {
                $duplicates[$number] = $names;
            }
        }
        
        return response()->json([
            'tenant_id' => $tenantId,
            'total_numbered_postes' => $numberedPostes->count(),
            'all_numbered_postes' => $numberedPostes->pluck('name')->toArray(),
            'duplicates_found' => $duplicates,
            'duplicate_count' => count($duplicates)
        ]);
    })->name('debug.poste-duplicates');
    
    // Security test route
    Route::get('/debug/security-test', function() {
        return response()->json([
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'user' => auth()->user()->only(['id', 'name', 'email', 'tenant_id']),
            'session_id' => session()->getId(),
            'csrf_token' => csrf_token(),
            'headers' => request()->headers->all()
        ]);
    })->name('debug.security-test');
});
