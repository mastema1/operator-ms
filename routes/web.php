<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StaticDashboardController;
use App\Http\Controllers\BackupAssignmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/home', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', \App\Livewire\Dashboard::class)->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/dashboard-static', [StaticDashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard.static');

// About Me page routes
Route::get('/about-me', function () {
    return response()->file(public_path('about-me.html'));
})->name('about-me');

Route::get('/about-me-prof', function () {
    return response()->file(public_path('about-me-prof.html'));
})->name('about-me-prof');

Route::get('/about-me-per', function () {
    return response()->file(public_path('about-me-per.html'));
})->name('about-me-per');

// Sick page route
Route::get('/sick', function () {
    return view('sick');
})->name('sick');

// Backup assignment API routes (operator-specific)
Route::post('/api/backup-assignments/assign', [BackupAssignmentController::class, 'assign'])->middleware(['auth', 'verified'])->name('backup.assign');
Route::delete('/api/backup-assignments/remove/{assignment}', [BackupAssignmentController::class, 'remove'])->middleware(['auth', 'verified'])->name('backup.remove');
Route::get('/api/backup-assignments/available-operators', [BackupAssignmentController::class, 'getAvailableOperators'])->middleware(['auth', 'verified'])->name('backup.operators');
Route::get('/api/backup-assignments/operator/{operator}', [BackupAssignmentController::class, 'getOperatorAssignment'])->middleware(['auth', 'verified'])->name('backup.operator');

Route::middleware('auth')->group(function () {
    Route::get('/operators', [\App\Http\Controllers\OperatorController::class, 'index'])->name('operators.index');
    Route::get('/api/operators', [\App\Http\Controllers\OperatorController::class, 'apiIndex'])->name('api.operators');
    Route::get('/absences', \App\Livewire\Absences::class)->name('absences.index');
    Route::get('/post-status', \App\Livewire\PostStatus::class)->name('post-status.index');
    Route::get('/postes', [\App\Http\Controllers\PosteController::class, 'index'])->name('postes.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Debug route for cache management (remove in production)
    Route::get('/debug/cache-status', function() {
        return response()->json(\App\Services\DashboardCacheManager::getCacheStats());
    })->name('debug.cache-status');
    
    Route::post('/debug/clear-cache', function() {
        \App\Services\DashboardCacheManager::clearDashboardCache();
        return response()->json(['message' => 'Dashboard cache cleared successfully']);
    })->name('debug.clear-cache');
    
    // Dashboard title management API
    Route::get('/api/dashboard/title', [\App\Http\Controllers\DashboardController::class, 'getTitle'])->name('api.dashboard.title.get');
    Route::post('/api/dashboard/title', [\App\Http\Controllers\DashboardController::class, 'updateTitle'])->name('api.dashboard.title.update');
    
    // Debug route for testing Golden Order sorting (remove in production)
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
    
    // Debug route for analyzing duplicate postes (remove in production)
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
    
    // Operators create/store/edit/update/destroy
    Route::get('/operators/create', [\App\Http\Controllers\OperatorController::class, 'create'])->name('operators.create');
    Route::post('/operators', [\App\Http\Controllers\OperatorController::class, 'store'])->name('operators.store');
    Route::get('/operators/{operator}/edit', [\App\Http\Controllers\OperatorController::class, 'edit'])->name('operators.edit');
    Route::put('/operators/{operator}', [\App\Http\Controllers\OperatorController::class, 'update'])->name('operators.update');
    Route::delete('/operators/{operator}', [\App\Http\Controllers\OperatorController::class, 'destroy'])->name('operators.destroy');

    // Postes create/store/edit/update/destroy
    Route::get('/postes/create', [\App\Http\Controllers\PosteController::class, 'create'])->name('postes.create');
    Route::post('/postes', [\App\Http\Controllers\PosteController::class, 'store'])->name('postes.store');
    Route::get('/postes/{poste}/edit', [\App\Http\Controllers\PosteController::class, 'edit'])->name('postes.edit');
    Route::put('/postes/{poste}', [\App\Http\Controllers\PosteController::class, 'update'])->name('postes.update');
    Route::delete('/postes/{poste}', [\App\Http\Controllers\PosteController::class, 'destroy'])->name('postes.destroy');

});

require __DIR__.'/auth.php';
