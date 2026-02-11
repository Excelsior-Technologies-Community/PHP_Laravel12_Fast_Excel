<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

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
    Route::get('/template', [ProductController::class, 'downloadTemplate'])->name('template');
});