<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OperatorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/home', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/operators', [\App\Http\Controllers\OperatorController::class, 'index'])->name('operators.index');
    Route::get('/absences', \App\Livewire\Absences::class)->name('absences.index');
    Route::get('/post-status', \App\Livewire\PostStatus::class)->name('post-status.index');
    Route::get('/postes', [\App\Http\Controllers\PosteController::class, 'index'])->name('postes.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

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
