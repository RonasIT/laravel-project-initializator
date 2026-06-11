<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use RonasIT\Clerk\Contracts\UserRepositoryContract;
use App\Support\Clerk\ClerkUserRepository;

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