<?php

namespace Demo\MyPackage;

use Illuminate\Support\ServiceProvider;

class MyPackageServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'my-package');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'my-package');
        $this->mergeConfigFrom(__DIR__.'/../config/my-package.php', 'my-package');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/my-package.php' => config_path('my-package.php'),
            ], 'my-package-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/my-package'),
            ], 'my-package-views');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'my-package-migrations');
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/my-package.php', 'my-package');
    }
}
