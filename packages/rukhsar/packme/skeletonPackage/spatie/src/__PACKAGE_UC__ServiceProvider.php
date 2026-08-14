<?php

namespace :VendorName\:PackageName;

use Illuminate\Support\ServiceProvider;

class __PACKAGE_UC__ServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', ':package_name');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', ':package_name');
        $this->mergeConfigFrom(__DIR__.'/../config/:package_name.php', ':package_name');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/:package_name.php' => config_path(':package_name.php'),
            ], ':package_name-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/:package_name'),
            ], ':package_name-views');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], ':package_name-migrations');
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/:package_name.php', ':package_name');
    }
}
