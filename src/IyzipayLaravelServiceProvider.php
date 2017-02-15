<?php

namespace Actuallymab\IyzipayLaravel;

use Illuminate\Support\ServiceProvider;

class IyzipayLaravelServiceProvider extends ServiceProvider
{

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('iyzipay.php')
        ]);

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/config.php',
            'iyzipay'
        );

        $this->app->bind('iyzipay-laravel', function () {
            return new IyzipayLaravel();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['iyzipay-laravel'];
    }
}
