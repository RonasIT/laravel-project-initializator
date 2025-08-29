<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use RonasIT\Clerk\Contracts\UserRepositoryContract;
use App\Support\Clerk\ClerkUserRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->app->bind(UserRepositoryContract::class, ClerkUserRepository::class);
    }
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
    }
}
