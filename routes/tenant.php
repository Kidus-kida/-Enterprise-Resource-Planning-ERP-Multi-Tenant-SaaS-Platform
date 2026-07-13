<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Modules\Superadmin\Models\Tenant;

Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('tenant.login', ['tenant' => request()->route('tenant')]);
    }

    return view('dashboard');
});

Route::get('/dashboard', function () {
    if (!Auth::check()) {
        return redirect()->route('tenant.login', ['tenant' => request()->route('tenant')]);
    }

    $tenantSlug = request()->route('tenant');
    $tenantModel = null;
    $enabledModules = [];

    try {
        $tenantModel = Tenant::where('id', $tenantSlug)
            ->orWhere('database_name', $tenantSlug)
            ->first();

        if ($tenantModel && $tenantModel->business) {
            $enabledModules = $tenantModel->business->resolveEnabledModules();
        }
    } catch (\Throwable $e) {
        $enabledModules = [];
    }

    return view('tenant.dashboard', compact('tenantSlug', 'tenantModel', 'enabledModules'));
})->name('tenant.dashboard');

Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'login')->name('tenant.login');
    Route::post('/login', 'loginAuth')->name('tenant.login.auth');
    Route::post('/logout', 'logout')->name('tenant.logout');

    Route::get('/forgot-password', 'forgotPassword')->name('tenant.password.email');
    Route::post('/forgot-password', 'sendResetLink')->name('tenant.password.request');
    Route::get('/reset-password/{token}', 'resetPassword')->name('tenant.password.reset');
    Route::post('/reset-password', 'updatePassword')->name('tenant.password.update');
});
