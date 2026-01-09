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
    Route::get('sales-return/add/{id}', [\Modules\Sales\Http\Controllers\SalesReturnController::class, 'add'])->name('sales-return.add');
    Route::resource('sales-return', \Modules\Sales\Http\Controllers\SalesReturnController::class);
    Route::get('get_products', [SalesController::class, 'getProducts'])->name('sales.get_products');
    Route::post('get_sell_entry_row', [SalesController::class, 'getSellEntryRow'])->name('sales.get_sell_entry_row');
    Route::get('pos/list', [PosController::class, 'list'])->name('sales.pos.list');
    Route::get('draft-list', [SalesController::class, 'draftIndex'])->name('sales.draft.index');
    Route::get('quotation-list', [SalesController::class, 'quotationIndex'])->name('sales.quotation.index');
    Route::get('over-limit-sales', [SalesController::class, 'overLimitSales'])->name('sales.over_limit_sales');
    Route::get('sells/subscriptions', [SalesController::class, 'listSubscriptions'])->name('sales.subscriptions.index');
    Route::get('sells/{id}/toggle-recurring-invoice', [SalesController::class, 'toggleRecurringInvoices'])->name('sales.subscriptions.toggle');

    Route::post('get_payment_row', [SalesController::class, 'getPaymentRow'])->name('sales.get_payment_row');
    Route::get('shipments', [SalesController::class, 'shipments'])->name('sales.shipments');
    Route::get('edit-shipping/{id}', [SalesController::class, 'editShipping'])->name('sales.edit_shipping');
    Route::put('update-shipping/{id}', [SalesController::class, 'updateShipping'])->name('sales.update_shipping');
    Route::get('get_payment_accounts', [SalesController::class, 'getPaymentAccounts'])->name('sales.get_payment_accounts');

    // POS Routes
    Route::get('pos/get_products', [PosController::class, 'getProducts'])->name('sales.pos.get_products');
    Route::get('pos/get_product_suggestion', [PosController::class, 'getProductSuggestion'])->name('sales.pos.get_product_suggestion');
    Route::post('pos/quick_add_contact', [PosController::class, 'quickAddContact'])->name('sales.pos.quick_add_contact');
    Route::get('pos/get_product_row', [PosController::class, 'getProductRow'])->name('sales.pos.get_product_row');
    Route::post('pos/get_customer_due_details', [PosController::class, 'getCustomerDueDetails'])->name('sales.pos.get_customer_due_details');
    Route::get('pos/get_payment_row', [PosController::class, 'getPaymentRow'])->name('sales.pos.get_payment_row');
    Route::get('pos/get_recent_transactions', [PosController::class, 'getRecentTransactions'])->name('sales.pos.get_recent_transactions');
    Route::get('pos/print_invoice/{transaction_id}', [PosController::class, 'printInvoice'])->name('sales.pos.print_invoice');
    Route::get('pos/get_payment_accounts', [PosController::class, 'getPaymentAccounts'])->name('sales.pos.get_payment_accounts');
    Route::get('pos/{id}/edit', [PosController::class, 'edit'])->name('sales.pos.edit');
    Route::put('pos/{id}', [PosController::class, 'update'])->name('sales.pos.update');
    Route::resource('pos', PosController::class)->names('sales.pos');
    
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
