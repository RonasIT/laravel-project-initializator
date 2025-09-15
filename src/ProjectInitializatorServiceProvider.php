<?php

namespace RonasIT\ProjectInitializator;

use Illuminate\Support\ServiceProvider;
use RonasIT\ProjectInitializator\Commands\InitCommand;

class ProjectInitializatorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([
            InitCommand::class,
        ]);

        $this->publishes([
            __DIR__ . '/../stubs/controllers/LoginController.php' => app_path('Http/Controllers/Auth/LoginController.php'),
            __DIR__ . '/../stubs/assets/css/app.css' => public_path('app.css'),
            __DIR__ . '/../stubs/assets/js/app.js' => public_path('app.js'),
            __DIR__ . '/../stubs/views/app.blade.php' => resource_path('views/layouts/app.blade.php'),
            __DIR__ . '/../stubs/views/login.blade.php' => resource_path('views/auth/login.blade.php'),
        ], 'initializator-web-login');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'initializator');
        $this->loadViewsFrom(__DIR__ . '/../stubs', 'project-initializator');
    }
}
