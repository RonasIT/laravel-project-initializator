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

    protected function assertFileEqualsFixture(string $fixture, string $fileName, bool $exportMode = false): void
    {
        $this->assertFileExists($fileName);

        $data = File::get($fileName);

        if ($this->globalExportMode || $exportMode) {
            $this->exportContent($data, $fixture);
        }

        $fixturePath = $this->prepareFixtureName($this->getFixturePath($fixture));
        $assertFailedMessage = "Failed asserting that the provided file {$fileName} equal to fixture: {$fixturePath}";

        $this->assertEquals(
            expected: $this->getFixture($fixture),
            actual: $data,
            message: $assertFailedMessage,
        );
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
