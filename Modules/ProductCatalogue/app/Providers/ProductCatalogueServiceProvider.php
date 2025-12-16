<?php

namespace Modules\ProductCatalogue\Providers;

use Illuminate\Support\ServiceProvider;

class ProductCatalogueServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path('ProductCatalogue', 'database/migrations'));
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path('ProductCatalogue', 'config/config.php') => config_path('productcatalogue.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path('ProductCatalogue', 'config/config.php'),
            'productcatalogue'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/productcatalogue');

        $sourcePath = module_path('ProductCatalogue', 'resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], 'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/productcatalogue';
        }, \Config::get('view.paths')), [$sourcePath]), 'productcatalogue');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/productcatalogue');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'productcatalogue');
        } else {
            $this->loadTranslationsFrom(module_path('ProductCatalogue', 'resources/lang'), 'productcatalogue');
        }
    }


    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
