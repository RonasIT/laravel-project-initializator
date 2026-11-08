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
            __DIR__ . '/../stubs/files/controllers/LoginController.php' => app_path('Http/Controllers/Auth/LoginController.php'),
            __DIR__ . '/../stubs/files/assets/css/app.css' => public_path('app.css'),
            __DIR__ . '/../stubs/files/assets/js/app.js' => public_path('app.js'),
            __DIR__ . '/../stubs/files/views/app.blade.php' => resource_path('views/layouts/app.blade.php'),
            __DIR__ . '/../stubs/files/views/login.blade.php' => resource_path('views/auth/login.blade.php'),
            __DIR__ . '/../stubs/files/routes/web.php' => base_path('routes/web.php'),
        ], 'initializator-web-login');

        $this->loadViewsFrom(__DIR__ . '/../stubs/templates', 'initializator');

        $this->publishes([
            __DIR__ . '/../stubs/files/pint.json' => base_path('pint.json'),
        ], 'pint-config');

        $this->publishes([
            __DIR__ . '/../stubs/files/requests/Request.php' => app_path('Http/Requests/Request.php'),
        ], 'base-request');

        $this->publishes([
            __DIR__ . '/../stubs/files/models/user_with_role.php' => app_path('Models/User.php'),
        ], 'initializator-user-model-with-role');

        $this->publishes([
            __DIR__ . '/../stubs/files/models/user_with_clerk.php' => app_path('Models/User.php'),
        ], 'initializator-user-model-with-clerk');

        $this->publishes([
            __DIR__ . '/../stubs/files/enums/RoleEnum.php' => app_path('Enums/User/RoleEnum.php'),
        ], 'initializator-role-enum');

        $this->publishes([
            __DIR__ . '/../stubs/files/support/ClerkUserRepository.php' => app_path('Support/Clerk/ClerkUserRepository.php'),
        ], 'initializator-clerk-user-repository');
    }
}
