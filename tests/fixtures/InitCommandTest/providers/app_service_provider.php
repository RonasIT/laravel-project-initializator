<?php

namespace App\Providers;

use App\Enums\VersionEnum;
use Illuminate\Support\ServiceProvider;
use RonasIT\Support\Contracts\VersionEnumContract;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(VersionEnumContract::class, fn () => VersionEnum::class);
    }

    public function boot(): void
    {
    }
}
