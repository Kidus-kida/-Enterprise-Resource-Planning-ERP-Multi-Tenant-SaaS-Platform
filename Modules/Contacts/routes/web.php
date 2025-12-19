<?php

use Illuminate\Support\Facades\Route;
use Modules\Contacts\Http\Controllers\ContactGroupController;
use Modules\Contacts\Http\Controllers\CustomerLoanController;
use Modules\Contacts\Http\Controllers\CustomerPaymentController;
use Modules\Contacts\Http\Controllers\SupplierMappingController;
use Modules\Contacts\Http\Controllers\CustomerStatementController;
use Modules\Contacts\Http\Controllers\ContactController;

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

    Route::resource('contacts', ContactController::class);
    
    Route::post('contact-groups/fetch-account', [ContactGroupController::class, 'fetchAccount'])->name('contact-groups.fetch-account');
    Route::resource('contact-groups', ContactGroupController::class);
    Route::resource('customer-loans', CustomerLoanController::class);
    Route::resource('customer-payments', CustomerPaymentController::class);
    
    // Supplier Mappings
    Route::get('add-supplier-map-product/get-supplier-mapped', [SupplierMappingController::class, 'getSupplierMapped']);
    Route::post('create_mappings', [SupplierMappingController::class, 'createMappings']); 
    Route::resource('supplier-mappings', SupplierMappingController::class);
    
    // Customer Statements
    Route::resource('customer-statements', CustomerStatementController::class);

    // Customer Reference
    Route::get('get-customer-reference/{id}', [\Modules\Contacts\Http\Controllers\CustomerReferenceController::class, 'getCustomerReference']);
    Route::post('get-customer-reference-barcode', [\Modules\Contacts\Http\Controllers\CustomerReferenceController::class, 'getCustomerReferenceBarcode']);
    Route::resource('customer-reference', \Modules\Contacts\Http\Controllers\CustomerReferenceController::class);

    // Contact Reports
    Route::get('outstanding-received-report', [\Modules\Contacts\Http\Controllers\ContactReportController::class, 'getOutstandingReceivedReport']);
    Route::get('issued-payment-details', [\Modules\Contacts\Http\Controllers\ContactReportController::class, 'getIssuedPaymentDetails']);
    Route::get('returned-cheques', [\Modules\Contacts\Http\Controllers\ContactReportController::class, 'getReturnedCheques']);

    // Contact Settings
    Route::get('contacts/settings', [\Modules\Contacts\Http\Controllers\ContactSettingsController::class, 'settings']);
    Route::post('contacts/save-settings', [\Modules\Contacts\Http\Controllers\ContactSettingsController::class, 'save_settings']);

    // Contact Import
    Route::get('contacts/import', [\Modules\Contacts\Http\Controllers\ContactImportController::class, 'getImportContacts'])->name('contacts.import');
    Route::post('contacts/import', [\Modules\Contacts\Http\Controllers\ContactImportController::class, 'postImportContacts']);
    Route::get('contacts/import-balance', [\Modules\Contacts\Http\Controllers\ContactImportController::class, 'getImportBalance'])->name('contacts.import.balance');
    Route::post('contacts/import-balance', [\Modules\Contacts\Http\Controllers\ContactImportController::class, 'postImportBalance']);

});
