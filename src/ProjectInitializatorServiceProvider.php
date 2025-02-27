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

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'initializator');
    }
}
