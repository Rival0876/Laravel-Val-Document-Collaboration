<?php

use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;

// Redirect root ke dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Routes yang butuh login
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DocumentController::class, 'index'])->name('dashboard');
    
    // Buat dokumen baru
    Route::post('/documents/create', [DocumentController::class, 'create'])->name('documents.create');
    
    // Editor dokumen
    Route::get('/documents/{document}', [DocumentController::class, 'edit'])->name('documents.edit');
    
    // Simpan versi (POST)
    Route::post('/documents/{document}/versions', [DocumentController::class, 'saveVersion'])
        ->name('documents.versions');
    
    // ✅ Ambil daftar versi (GET)
    Route::get('/documents/{document}/versions', [DocumentController::class, 'getVersions'])
        ->name('documents.versions.list');
    
    // ✅ Ambil satu versi untuk preview (GET) - INI YANG TADI HILANG!
    Route::get('/documents/{document}/versions/{version}', [DocumentController::class, 'getVersion'])
        ->name('documents.versions.show');
    
    // Restore versi (POST)
    Route::post('/documents/{document}/versions/{version}/restore', [DocumentController::class, 'restoreVersion'])
        ->name('documents.versions.restore');
    
    // Rename dokumen
    Route::post('/documents/{document}/rename', [DocumentController::class, 'rename'])->name('documents.rename');
});

require __DIR__.'/auth.php';