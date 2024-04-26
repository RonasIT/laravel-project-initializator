<?php

namespace RonasIT\LaravelProjectInitializator;

use RonasIT\LaravelProjectInitializator\Commands\Init;
use RonasIT\LaravelProjectInitializator\Commands\GenerateDefaultUserMigration;
use Illuminate\Support\ServiceProvider;

class LaravelProjectInitializatorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-project-initializator');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-project-initializator');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('laravel-project-initializator.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-project-initializator'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/laravel-project-initializator'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/laravel-project-initializator'),
            ], 'lang');*/

            // Registering package commands.
             $this->commands([Init::class, GenerateDefaultUserMigration::class]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'laravel-project-initializator');

        // Register the main class to use with the facade
        $this->app->singleton('laravel-project-initializator', function () {
            return new LaravelProjectInitializator;
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                Init::class,
                GenerateDefaultUserMigration::class,
            ]);
        }
    }
}
