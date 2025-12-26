<?php

use Illuminate\Support\Facades\Route;
use Modules\Sales\Http\Controllers\SalesController;
use Modules\Sales\Http\Controllers\TaxesController;
use Modules\Sales\Http\Controllers\ExpensesController;
use Modules\Sales\Http\Controllers\InvoicesController;
use Modules\Sales\Http\Controllers\EstimatesController;
use Modules\Sales\Http\Controllers\PosController;
use Modules\Sales\Http\Controllers\CashRegisterController;

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
    Route::get('list', [SalesController::class, 'list'])->name('sales.list');
    Route::resource('sales', SalesController::class);
    Route::get('get_products', [SalesController::class, 'getProducts'])->name('sales.get_products');
    Route::post('get_sell_entry_row', [SalesController::class, 'getSellEntryRow'])->name('sales.get_sell_entry_row');
    Route::post('get_payment_row', [SalesController::class, 'getPaymentRow'])->name('sales.get_payment_row');

    // POS Routes
    Route::get('pos/get_products', [PosController::class, 'getProducts'])->name('sales.pos.get_products');
    Route::get('pos/get_product_suggestion', [PosController::class, 'getProductSuggestion'])->name('sales.pos.get_product_suggestion');
    Route::get('pos/get_product_row', [PosController::class, 'getProductRow'])->name('sales.pos.get_product_row');
    Route::resource('pos', PosController::class)->names('sales.pos')->only(['create', 'store']);
    
    Route::resource('cash-register', CashRegisterController::class)->names('sales.cash-register');
    Route::get('cash-register/register-details', [CashRegisterController::class, 'getRegisterDetails'])->name('sales.cash-register.details');
    Route::get('cash-register/close-register', [CashRegisterController::class, 'getCloseRegister'])->name('sales.cash-register.close');
    Route::post('cash-register/close-register', [CashRegisterController::class, 'postCloseRegister'])->name('sales.cash-register.post-close');

    Route::get('stores-by-location/{locationId}', function ($locationId) {
        $stores = \App\Models\Store::where('location_id', $locationId)
                    ->pluck('name', 'id')
                    ->toArray();
        return response()->json($stores);
    })->name('sales.stores.by.location');

    Route::resource('taxes', TaxesController::class);
    Route::resource('expenses', ExpensesController::class)->except('show');
    Route::resource('estimates', EstimatesController::class);
    Route::delete('estimate-item/{item}', [EstimatesController::class, 'destroyItem'])->name('estimate-item.destroy');
    Route::any('estimate-pdf/{estimate}', [EstimatesController::class, 'downloadPdf'])->name('estimate.pdf');
    Route::resource('invoices', InvoicesController::class);
});
