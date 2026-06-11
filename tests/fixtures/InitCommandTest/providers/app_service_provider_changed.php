<?php

namespace App\Providers;

use App\Enums\VersionEnum;
use Illuminate\Support\ServiceProvider;
use RonasIT\Support\Contracts\VersionEnumContract;
use RonasIT\TelescopeExtension\Contracts\ReportNotificationContract;
use RonasIT\ProjectInitializator\Generators\TelescopeReportNotification;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(VersionEnumContract::class, fn () => VersionEnum::class);

        $this->app->bind(ReportNotificationContract::class, TelescopeReportNotification::class);
    }

    public function boot(): void
    {
    }
}
