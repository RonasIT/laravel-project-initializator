<?php

namespace RonasIT\ProjectInitializator\Tests;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use RonasIT\ProjectInitializator\ProjectInitializatorServiceProvider;
use RonasIT\Support\Traits\FixturesTrait;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    use FixturesTrait;

    public function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2018-11-11 11:11:11');
    }

    protected function defineEnvironment($app): void
    {
        $app->setBasePath(__DIR__ . '/..');
    }

    protected function getPackageProviders($app): array
    {
        return [
            ProjectInitializatorServiceProvider::class,
        ];
    }

    protected function getReadmeTemplateContent(string $templateName): string
    {
        return file_get_contents(base_path("/resources/md/readme/{$templateName}"));
    }

    protected function generateResourcePath(string $path): string
    {
        return base_path("/vendor/ronasit/laravel-project-initializator/resources/{$path}");
    }
}
