<?php

namespace RonasIT\ProjectInitializator\Tests;

use Carbon\Carbon;
use Orchestra\Testbench\TestCase as BaseTestCase;
use RonasIT\ProjectInitializator\ProjectInitializatorServiceProvider;
use RonasIT\Support\Traits\FixturesTrait;

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
}
