<?php

use Illuminate\Support\Facades\Route;
use Modules\Logistics\Http\Controllers\DashboardController;
use Modules\Logistics\Http\Controllers\ShipmentsController;
use Modules\Logistics\Http\Controllers\ContainersController;
use Modules\Logistics\Http\Controllers\CustomsController;
use Modules\Logistics\Http\Controllers\TransportController;
use Modules\Logistics\Http\Controllers\DocumentsController;
use Modules\Logistics\Http\Controllers\CalculatorController;
use Modules\Logistics\Http\Controllers\ReportsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('logistics')->middleware(['auth', 'verified'])->name('logistics.')->group(function() {
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Shipments
    Route::resource('shipments', ShipmentsController::class);
    
    // Containers
    Route::resource('containers', ContainersController::class);
    
    // Customs
    Route::resource('customs', CustomsController::class);
    
    // Transport
    Route::resource('transport', TransportController::class);
    
    // Documents
    // Documents
    Route::get('documents', [DocumentsController::class, 'index'])->name('documents.index');
    Route::get('documents/create', [DocumentsController::class, 'create'])->name('documents.create');
    Route::post('documents', [DocumentsController::class, 'store'])->name('documents.store');
    Route::get('documents/{document}/download', [DocumentsController::class, 'download'])->name('documents.download');
    Route::get('documents/{document}/approve', [DocumentsController::class, 'approve'])->name('documents.approve');
    Route::delete('documents/{document}', [DocumentsController::class, 'destroy'])->name('documents.destroy');
    
    // Calculator
    Route::get('calculator', [CalculatorController::class, 'index'])->name('calculator.index');
    Route::post('calculator/calculate', [CalculatorController::class, 'calculate'])->name('calculator.calculate');
    
    // Reports
    Route::get('reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::post('reports/generate', [ReportsController::class, 'generate'])->name('reports.generate');

});
