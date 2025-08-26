<?php

namespace RonasIT\ProjectInitializator;

use Illuminate\Support\ServiceProvider;
use RonasIT\ProjectInitializator\Commands\InitCommand;
use RonasIT\ProjectInitializator\Support\Parser\PhpParser;
use RonasIT\ProjectInitializator\Support\Parser\Factories\PhpParserFactory;

class ProjectInitializatorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([
            InitCommand::class,
        ]);

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'initializator');

        $this->app->bind(PhpParser::class, function ($app, $params) {
            return PhpParserFactory::create($params['filePath']);
        });
    }
}
