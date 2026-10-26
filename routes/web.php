<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\FileHistoryController;

Route::get('/', function () {
    return redirect()->route('products.index');
});

Route::prefix('products')->name('products.')->group(function () {
    // Dashboard & Listing
    Route::get('/', [ProductController::class, 'index'])->name('index');

    // Advanced Export Routes
    Route::get('/export', [ProductController::class, 'export'])->name('export');
    Route::get('/export/multisheet', [ProductController::class, 'exportMultiSheet'])->name('export.multisheet');
    Route::get('/export/pdf', [ProductController::class, 'exportPdf'])->name('export.pdf');

    // Smart Import Routes
    Route::get('/import', [ProductController::class, 'showImportForm'])->name('import.form');
    Route::post('/import/map', [ProductController::class, 'uploadAndMap'])->name('import.map');
    Route::post('/import/process', [ProductController::class, 'processImport'])->name('import.process');
    Route::get('/import/progress/{id}', [ProductController::class, 'importProgress'])->name('import.progress');

    // Bulk Image ZIP Import
    Route::post('/import/images-zip', [ProductController::class, 'importImagesZip'])->name('import.images');

    // Sample Template Download
    Route::get('/template', [ProductController::class, 'downloadTemplate'])->name('template');

    // Low Stock Alert
    Route::post('/low-stock/alert', [ProductController::class, 'triggerLowStockAlert'])->name('lowstock.alert');
});

// Import / Export History & One-Click Rollback
Route::prefix('files')->name('files.')->group(function () {
    Route::get('/history', [FileHistoryController::class, 'index'])->name('history');
    Route::post('/history/{id}/rollback', [FileHistoryController::class, 'rollback'])->name('history.rollback');
});
