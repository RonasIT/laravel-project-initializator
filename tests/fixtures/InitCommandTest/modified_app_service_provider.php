<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->app->bind(RonasIT\Clerk\Contracts\UserRepositoryContract::class, ClerkUserRepository::class);
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