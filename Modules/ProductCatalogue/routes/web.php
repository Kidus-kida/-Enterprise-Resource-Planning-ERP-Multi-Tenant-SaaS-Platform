<?php

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

Route::group(['namespace' => '\Modules\ProductCatalogue\Http\Controllers', 'middleware' => ['tenant.context']], function () {
    Route::get('/catalogue/{business_id}/{location_id}', 'ProductCatalogueController@index')->name('catalogue.index');
    Route::get('/show-catalogue/{business_id}/{product_id}', 'ProductCatalogueController@show')->name('catalogue.show');
});

Route::group(['middleware' => ['web', 'auth', 'auth', 'SetSessionData', 'language', 'timezone', 'tenant.context'], 'namespace' => '\Modules\ProductCatalogue\Http\Controllers', 'prefix' => 'product-catalogue'], function () {
    Route::get('catalogue-qr', 'ProductCatalogueController@generateQr')->name('product-catalogue.catalogue-qr');

    Route::get('install', 'InstallController@index')->name('product-catalogue.install');
    Route::post('install', 'InstallController@install')->name('product-catalogue.install.post');
    Route::get('install/uninstall', 'InstallController@uninstall')->name('product-catalogue.uninstall');
    Route::get('install/update', 'InstallController@update')->name('product-catalogue.update');
    Route::post('checkout', 'CartController@checkout')->name('product-catalogue.checkout');
    Route::resource('cart', 'CartController');
});
