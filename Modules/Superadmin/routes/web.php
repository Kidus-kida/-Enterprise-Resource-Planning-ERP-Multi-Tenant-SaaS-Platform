<?php

use Illuminate\Support\Facades\Route;
use Modules\Superadmin\Http\Controllers\SuperadminController;
use Modules\Superadmin\Http\Controllers\BusinessController;
use Modules\Superadmin\Http\Controllers\PackagesController;
use Modules\Superadmin\Http\Controllers\SubscriptionsController;
use Modules\Superadmin\Http\Controllers\TenantManagementController;
use Modules\Superadmin\Http\Controllers\ManualPaymentController;
use Modules\Superadmin\Http\Controllers\SuperadminSettingsController;
use Modules\Superadmin\Http\Controllers\ModuleController;
use Modules\Superadmin\Http\Controllers\AddonController;

/*
|--------------------------------------------------------------------------
| Superadmin Module Routes
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'superadmin', 'middleware' => ['auth', 'superadmin', 'central_domain']], function () {

    // Dashboard
    Route::get('/', [SuperadminController::class, 'index'])->name('superadmin.dashboard');

    // Business Management
    Route::resource('businesses', BusinessController::class)->names('superadmin.businesses');
    Route::post('businesses/{business}/activate', [BusinessController::class, 'activate'])->name('superadmin.businesses.activate');
    Route::post('businesses/{business}/deactivate', [BusinessController::class, 'deactivate'])->name('superadmin.businesses.deactivate');
    Route::post('businesses/{id}/resend-invite', [BusinessController::class, 'resendInvite'])->name('superadmin.businesses.resend-invite');

    // Module Management
    Route::resource('modules', ModuleController::class)->names('superadmin.modules');
    Route::post('modules/{module}/toggle-active', [ModuleController::class, 'toggleActive'])->name('superadmin.modules.toggle-active');

    // Add-on Management
    Route::resource('addons', AddonController::class)->names('superadmin.addons');
    Route::post('addons/{addon}/toggle-active', [AddonController::class, 'toggleActive'])->name('superadmin.addons.toggle-active');

    // Package Management
    Route::resource('packages', PackagesController::class)->names('superadmin.packages');
    Route::post('packages/{package}/toggle-active', [PackagesController::class, 'toggleActive'])->name('superadmin.packages.toggle-active');

    // Subscription Management
    Route::resource('subscriptions', SubscriptionsController::class)->names('superadmin.subscriptions');
    Route::post('subscriptions/{subscription}/approve', [SubscriptionsController::class, 'approve'])->name('superadmin.subscriptions.approve');
    Route::post('subscriptions/{subscription}/decline', [SubscriptionsController::class, 'decline'])->name('superadmin.subscriptions.decline');
    Route::post('subscriptions/{subscription}/renew', [SubscriptionsController::class, 'renew'])->name('superadmin.subscriptions.renew');

    // Tenant Management
    Route::prefix('tenant-management')->name('superadmin.tenant-management.')->group(function () {
        Route::get('/', [TenantManagementController::class, 'index'])->name('index');
        Route::get('/setup-wizard/{businessId}', [TenantManagementController::class, 'setupWizard'])->name('setup-wizard');
        Route::post('/verify-connection/{tenantId}', [TenantManagementController::class, 'verifyConnection'])->name('verify-connection');
        Route::post('/run-migrations/{tenantId}', [TenantManagementController::class, 'runMigrations'])->name('run-migrations');
        Route::post('/clear-permission-cache/{tenantId}', [TenantManagementController::class, 'clearPermissionCache'])->name('clear-permission-cache');
        Route::delete('/{tenantId}', [TenantManagementController::class, 'destroy'])->name('destroy');
    });

    // Manual Payment Management
    Route::prefix('manual-payments')->name('superadmin.manual-payments.')->group(function () {
        Route::get('/', [ManualPaymentController::class, 'index'])->name('index');
        Route::get('/pending', [ManualPaymentController::class, 'pending'])->name('pending');
        Route::post('/{payment}/approve', [ManualPaymentController::class, 'approve'])->name('approve');
        Route::post('/{payment}/reject', [ManualPaymentController::class, 'reject'])->name('reject');
        Route::get('/{payment}', [ManualPaymentController::class, 'show'])->name('show');
    });

    Route::prefix('settings')->name('superadmin.settings.')->group(function () {

        // Root — redirect to general
        Route::get('/', [SuperadminSettingsController::class, 'index'])->name('index');

        // Utility routes (must stay BEFORE the {section} wildcard)
        Route::get('export', [SuperadminSettingsController::class, 'export'])->name('export');
        Route::post('import', [SuperadminSettingsController::class, 'import'])->name('import');
        Route::post('media/upload', [SuperadminSettingsController::class, 'uploadMedia'])->name('media.upload');
        Route::get('search', [SuperadminSettingsController::class, 'search'])->name('search');
        Route::get('logs/audit', [SuperadminSettingsController::class, 'auditLogs'])->name('audit-logs');
        Route::post('email/test', [SuperadminSettingsController::class, 'testEmail'])->name('email.test');
        Route::post('maintenance/artisan', [SuperadminSettingsController::class, 'runArtisan'])->name('maintenance.artisan');
        Route::post('maintenance/clear-cache', [SuperadminSettingsController::class, 'clearCache'])->name('maintenance.clear-cache');

        // Menu Builder — dedicated save endpoint
        Route::post('menu/save', [SuperadminSettingsController::class, 'saveMenu'])->name('menu.save');

        // Dashboard Builder — dedicated save endpoint
        Route::post('dashboard/save', [SuperadminSettingsController::class, 'saveDashboard'])->name('dashboard.save');

        // Section routes (wildcard — must be LAST)
        Route::get('{section}', [SuperadminSettingsController::class, 'show'])->name('show');
        Route::post('{section}', [SuperadminSettingsController::class, 'update'])->name('update');
        Route::post('{section}/restore-defaults', [SuperadminSettingsController::class, 'restoreDefaults'])->name('restore-defaults');
    });
});
