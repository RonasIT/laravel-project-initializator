<?php

namespace RonasIT\ProjectInitializator\Tests;

use Carbon\Carbon;
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

    protected function assertEqualsFixture(string $fixture, $data, bool $exportMode = false): void
    {
        if ($this->globalExportMode || $exportMode) {
            $this->exportJson($fixture, $data);
        }

        $fixturePath = $this->prepareFixtureName($this->getFixturePath($fixture));
        $assertFailedMessage = "Failed asserting that the provided data equal to fixture: {$fixturePath}";

        $this->assertEquals(
            expected: $this->getFixture($fixture),
            actual: $data,
            message: $assertFailedMessage,
        );
    }
}
