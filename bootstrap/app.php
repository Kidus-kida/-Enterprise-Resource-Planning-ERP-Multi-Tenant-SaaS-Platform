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
        
        /*
         * MULTI-TENANT MIDDLEWARE PRIORITY
         * ================================
         * This priority configuration is ESSENTIAL for tenant authentication to work.
         * 
         * Problem: Laravel's default middleware priority runs Authenticate before custom middleware.
         * This caused redirect loops because Auth checked the Master DB before tenant DB was configured.
         * 
         * Solution: Force SwitchTenantDatabase to run BEFORE Authenticate.
         * 
         * Order:
         * 1. StartSession - Session must be available to read tenant ID
         * 2. ShareErrorsFromSession - For validation error display
         * 3. SwitchTenantDatabase - Configures tenant database connection from session
         * 4. Authenticate - Now correctly validates user against tenant database
         * 
         * DO NOT REMOVE - Removing this will break tenant login (causes ERR_TOO_MANY_REDIRECTS)
         */
        $middleware->priority([
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\SwitchTenantDatabase::class,
            \Illuminate\Auth\Middleware\Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
