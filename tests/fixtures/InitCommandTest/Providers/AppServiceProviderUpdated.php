<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Support\Clerk\ClerkUserRepository;
use RonasIT\Clerk\Contracts\UserRepositoryContract;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {

    }

    public function boot(): void
    {
        $this->app->bind(UserRepositoryContract::class, ClerkUserRepository::class);
    }
}