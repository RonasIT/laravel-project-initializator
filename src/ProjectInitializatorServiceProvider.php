<?php

namespace RonasIT\Support;

use Illuminate\Support\ServiceProvider;
use RonasIT\Support\Commands\InitCommand;

class ProjectInitializatorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([
            InitCommand::class,
        ]);

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'initializator');
    }
}
