<?php

use Illuminate\Support\Facades\Route;
use Modules\Purchase\Http\Controllers\PurchaseController;
use Modules\Purchase\Http\Controllers\SupplierController;
use Modules\Purchase\Http\Controllers\PurchaseReturnController;

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
    Route::get('purchase-list-json', [PurchaseController::class, 'list'])->name('purchase.list');
    Route::get('purchase/shipments', [PurchaseController::class, 'shipments'])->name('purchase.shipments');
    Route::get('purchase/edit-shipping/{id}', [PurchaseController::class, 'editShipping'])->name('purchase.edit_shipping');
    Route::put('purchase/update-shipping/{id}', [PurchaseController::class, 'updateShipping'])->name('purchase.update_shipping');

    // AJAX routes for purchase creation
    Route::post('purchases/get_purchase_entry_row', [PurchaseController::class, 'getPurchaseEntryRow'])->name('purchase.get_purchase_entry_row');
    Route::post('purchases/get_payment_row', [PurchaseController::class, 'getPaymentRow'])->name('purchase.get_payment_row');
    Route::get('purchases/get_products', [PurchaseController::class, 'getProducts'])->name('purchase.get_products');
    Route::get('purchases/get_suppliers', [PurchaseController::class, 'getSuppliers'])->name('purchase.get_suppliers');
    Route::get('purchases/get_payment_accounts', [PurchaseController::class, 'getPaymentAccounts'])->name('purchase.get_payment_accounts');
    Route::get('purchases/cheque-list', [PurchaseController::class, 'getChequeList'])->name('purchase.cheque_list');
    
    // Bulk Purchase Import
    Route::get('purchase/bulk-import', [PurchaseController::class, 'bulkImport'])->name('purchase.bulk_import');
    Route::post('purchase/bulk-import', [PurchaseController::class, 'bulkImportPost'])->name('purchase.bulk_import_post');
    Route::get('purchase/download-template', [PurchaseController::class, 'downloadTemplate'])->name('purchase.download_template');
    
    Route::get('purchase-return/add/{id}', [PurchaseReturnController::class, 'add'])->name('purchase-return.add');
    Route::resource('purchase-return', PurchaseReturnController::class);

    Route::resource('purchase', PurchaseController::class);

    Route::get('/purchase/stores-by-location/{locationId}', function ($locationId) {
    // Replace this with your actual logic
    $stores = \App\Models\Store::where('location_id', $locationId)
                ->pluck('name', 'id')
                ->toArray();

        return response()->json($stores);
    })->name('purchase.stores.by.location');
        
    Route::get('/getProductsPurchases', [PurchaseController::class, 'getProductsPurchases'])
     ->name('purchase.getProductsPurchases');
});
