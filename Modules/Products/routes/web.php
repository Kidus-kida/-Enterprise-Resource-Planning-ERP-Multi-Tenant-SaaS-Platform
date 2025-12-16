<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for the Products module.
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group.
|
*/

use Modules\Products\Http\Controllers\ProductController;
use Modules\Products\Http\Controllers\VariationTemplateController;
use Modules\Products\Http\Controllers\BrandController;
use Modules\Products\Http\Controllers\CategoryController;
use Modules\Products\Http\Controllers\UnitController;
use Modules\Products\Http\Controllers\SellingPriceGroupController;
use Modules\Products\Http\Controllers\MergedSubCategoryController;

Route::middleware(['web', 'auth'])->prefix('products')->name('products.')->group(function () {

    // Stock adjustment route (special, no auth middleware needed for this one)
    Route::get('/adjust', [ProductController::class, 'adjustStock'])->name('adjust')->withoutMiddleware(['auth']);

    // Stock history routes
    Route::get('/stock-history/{id}', [ProductController::class, 'productStockHistory'])->name('stock-history');
    Route::get('/stock-history/get-stores/{id}', [ProductController::class, 'productGetStore'])->name('stock-history.get-stores');
    Route::get('/tank-stock-history/{id}', [ProductController::class, 'productTankStockHistory'])->name('tank-stock-history');
    Route::get('/stock-history/get-tanks/{id}', [ProductController::class, 'productGetTank'])->name('stock-history.get-tanks');

    // Product disable/activate routes
    Route::get('/disable/{id}', [ProductController::class, 'disable'])->name('disable');
    Route::post('/disable', [ProductController::class, 'saveDisable'])->name('disable.save');
    Route::get('/activate/{id}', [ProductController::class, 'activate'])->name('activate');

    // Export/Download routes
    Route::get('/download-excel', [ProductController::class, 'downloadExcel'])->name('download-excel');

    // Mass operations
    Route::post('/mass-deactivate', [ProductController::class, 'massDeactivate'])->name('mass-deactivate');
    Route::post('/mass-delete', [ProductController::class, 'massDestroy'])->name('mass-delete');

    // Pricing routes
    Route::get('/view-product-group-price/{id}', [ProductController::class, 'viewGroupPrice'])->name('view-group-price');
    Route::get('/add-selling-prices/{id}', [ProductController::class, 'addSellingPrices'])->name('add-selling-prices');
    Route::post('/save-selling-prices', [ProductController::class, 'saveSellingPrices'])->name('save-selling-prices');
    Route::get('/min-selling-prices/{id}', [ProductController::class, 'minSellPrice'])->name('min-selling-prices');
    Route::post('/min-selling-prices-update', [ProductController::class, 'minSellPriceUpdate'])->name('min-selling-prices-update');

    // Product view and listing routes
    Route::get('/view/{id}', [ProductController::class, 'view'])->name('view');
    Route::get('/list', [ProductController::class, 'getProducts'])->name('list');
    Route::get('/list-sa', [ProductController::class, 'getProductsStockAdjustment'])->name('list-sa');
    Route::get('/list-pos', [ProductController::class, 'getProductsPos'])->name('list-pos');
    Route::get('/list-no-variation', [ProductController::class, 'getProductsWithoutVariations'])->name('list-no-variation');

    // Bulk edit routes
    Route::post('/bulk-edit', [ProductController::class, 'bulkEdit'])->name('bulk-edit');
    Route::post('/bulk-update', [ProductController::class, 'bulkUpdate'])->name('bulk-update');
    Route::post('/bulk-update-location', [ProductController::class, 'updateProductLocation'])->name('bulk-update-location');

    // Product editing helpers
    Route::get('/get-product-to-edit/{product_id}', [ProductController::class, 'getProductToEdit'])->name('get-product-to-edit');

    // Category and subcategory routes
    Route::post('/get_sub_categories', [ProductController::class, 'getSubCategories'])->name('get-sub-categories');
    Route::post('/get_product_category_wise', [ProductController::class, 'getProductsCategoryWise'])->name('get-product-category-wise');

    // Unit routes
    Route::get('/get_sub_units', [ProductController::class, 'getSubUnits'])->name('get-sub-units');

    // Variation routes
    Route::post('/product_form_part', [ProductController::class, 'getProductVariationFormPart'])->name('product-form-part');
    Route::post('/get_product_variation_row', [ProductController::class, 'getProductVariationRow'])->name('get-product-variation-row');
    Route::post('/get_variation_template', [ProductController::class, 'getVariationTemplate'])->name('get-variation-template');
    Route::get('/get_variation_value_row', [ProductController::class, 'getVariationValueRow'])->name('get-variation-value-row');

    // SKU validation
    Route::post('/check_product_sku', [ProductController::class, 'checkProductSku'])->name('check-product-sku');

    // Quick add product
    Route::get('/quick_add', [ProductController::class, 'quickAdd'])->name('quick-add');
    Route::post('/save_quick_product', [ProductController::class, 'saveQuickProduct'])->name('save-quick-product');

    // Combo product
    Route::get('/get-combo-product-entry-row', [ProductController::class, 'getComboProductEntryRow'])->name('get-combo-product-entry-row');

    // User activity report
    Route::get('/user_activity', [ProductController::class, 'getUserActivityReport'])->name('user-activity');

    // Variation Template
    Route::resource('variations', VariationTemplateController::class);

    // Brand
    Route::resource('brands', BrandController::class);

    // Category
    Route::resource('categories', CategoryController::class);

    // Unit
    Route::get('/units/get-sub-units', [UnitController::class, 'getSubUnits'])->name('units.get-sub-units');
    Route::resource('units', UnitController::class);

    // Selling Price Group
    Route::get('/selling-price-group/export', [SellingPriceGroupController::class, 'export'])->name('selling-price-group.export');
    Route::post('/selling-price-group/import', [SellingPriceGroupController::class, 'import'])->name('selling-price-group.import');
    Route::get('/selling-price-group/toggle-activate/{id}', [SellingPriceGroupController::class, 'toggleActivate'])->name('selling-price-group.toggle-activate');
    Route::resource('selling-price-group', SellingPriceGroupController::class);

    // Merged Sub Categories
    Route::get('/merged-sub-categories/get-sub-categories/{id}', [MergedSubCategoryController::class, 'getSubCategories'])->name('merged-sub-categories.get-sub-categories');
    Route::resource('merged-sub-categories', MergedSubCategoryController::class);

    // Resource routes (index, create, store, show, edit, update, destroy)
    Route::resource('/', ProductController::class)->parameters(['' => 'product']);
});
