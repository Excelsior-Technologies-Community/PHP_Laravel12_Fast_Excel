<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\FileHistoryController;

Route::get('/', function () {
    return redirect()->route('products.index');
});

Route::prefix('products')->name('products.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/export', [ProductController::class, 'export'])->name('export');

    Route::get('/export-simple', [ProductController::class, 'exportSimple'])->name('export.simple');

    Route::get('/export-csv', [ProductController::class, 'exportCsv'])->name('export.csv');

    Route::get('/import', [ProductController::class, 'showImportForm'])->name('import.form');
    Route::post('/import', [ProductController::class, 'import'])->name('import');

    // Import Preview
    Route::post('/import-preview', [ProductController::class, 'previewImport'])->name('import.preview');
    Route::get('/template', [ProductController::class, 'downloadTemplate'])->name('template');
});

// Import / Export History
Route::get('/files/history', [FileHistoryController::class,'index'])->name('files.history');
