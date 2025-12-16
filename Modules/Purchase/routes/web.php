<?php

use Illuminate\Support\Facades\Route;
use Modules\Purchase\Http\Controllers\PurchaseController;
use Modules\Purchase\Http\Controllers\SupplierController;

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

Route::middleware(['web', 'auth'])->group(function () {
    // AJAX routes for purchase creation
    Route::post('purchases/get_purchase_entry_row', [PurchaseController::class, 'getPurchaseEntryRow'])->name('purchase.get_purchase_entry_row');
    Route::post('purchases/get_payment_row', [PurchaseController::class, 'getPaymentRow'])->name('purchase.get_payment_row');
    Route::get('purchases/get_products', [PurchaseController::class, 'getProducts'])->name('purchase.get_products');
    Route::get('purchases/get_suppliers', [PurchaseController::class, 'getSuppliers'])->name('purchase.get_suppliers');
    Route::get('purchases/cheque-list', [PurchaseController::class, 'getChequeList'])->name('purchase.cheque_list');
    
    Route::resource('purchase', PurchaseController::class);
    Route::resource('supplier', SupplierController::class);
});
