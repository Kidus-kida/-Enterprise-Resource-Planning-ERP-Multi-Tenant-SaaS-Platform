<?php

use Illuminate\Support\Facades\Route;
use Modules\Contacts\Http\Controllers\ContactGroupController;
use Modules\Contacts\Http\Controllers\CustomerLoanController;
use Modules\Contacts\Http\Controllers\CustomerPaymentController;
use Modules\Contacts\Http\Controllers\SupplierMappingController;
use Modules\Contacts\Http\Controllers\CustomerStatementController;
use Modules\Contacts\Http\Controllers\ContactsController;

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

Route::middleware('auth')->group(function () {
// Route::resource('contacts', ContactsController::class);
    
    Route::resource('contact-groups', ContactGroupController::class);
    Route::resource('customer-loans', CustomerLoanController::class);
    Route::resource('customer-payments', CustomerPaymentController::class);
    Route::resource('supplier-mappings', SupplierMappingController::class);
    Route::resource('customer-statements', CustomerStatementController::class);
});
