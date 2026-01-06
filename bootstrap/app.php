<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use LaravelLang\Routes\Middlewares\LocalizationByCookie;
use LaravelLang\Routes\Middlewares\LocalizationByHeader;
use LaravelLang\Routes\Middlewares\LocalizationByModel;
use LaravelLang\Routes\Middlewares\LocalizationByParameter;
use LaravelLang\Routes\Middlewares\LocalizationByParameterWithRedirect;
use LaravelLang\Routes\Middlewares\LocalizationBySession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'localization.parameter' => LocalizationByParameter::class,
            'localization.redirect'  => LocalizationByParameterWithRedirect::class,
            'localization.header'    => LocalizationByHeader::class,
            'localization.cookie'    => LocalizationByCookie::class,
            'localization.session'   => LocalizationBySession::class,
            'localization.model'     => LocalizationByModel::class,

            //  Spatie permission middleware
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,

            // Superadmin module middleware
            'superadmin' => \Modules\Superadmin\Http\Middleware\SuperadminMiddleware::class,
            'subscription.check' => \Modules\Superadmin\Http\Middleware\CheckSubscription::class,
            'module.access' => \Modules\Superadmin\Http\Middleware\CheckModuleAccess::class,
        ]);
        
        $middleware->web(append: [
            \App\Http\Middleware\SwitchTenantDatabase::class,
        ]);
        
        // CRITICAL: Ensure SwitchTenantDatabase runs BEFORE Authenticate middleware
        // Laravel has a built-in priority where Authenticate runs first by default
        $middleware->priority([
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\SwitchTenantDatabase::class,  // Run BEFORE Auth
            \Illuminate\Auth\Middleware\Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
