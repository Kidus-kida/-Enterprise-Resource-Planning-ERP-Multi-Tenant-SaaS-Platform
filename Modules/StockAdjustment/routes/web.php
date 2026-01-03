<?php

use Illuminate\Support\Facades\Route;
use Modules\StockAdjustment\Http\Controllers\StockAdjustmentSettings;
use Modules\StockAdjustment\Http\Controllers\StockAdjustmentController;
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

Route::group([], function () {
    Route::resource('stockadjustment', StockAdjustmentController::class)
        ->names('stock_adjustment');

    // Resource routes for settings
    Route::resource('stockadjustment-settings', StockAdjustmentSettings::class)
        ->names('stockadjustment-settings');
});
