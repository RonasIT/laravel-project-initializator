<?php

namespace RonasIT\ProjectInitializator\Tests\Support\Traits;

use RonasIT\Support\Traits\MockTrait;

trait InitCommandMockTrait
{
    use MockTrait;

    protected function callClassExists(string $class, bool $result = true): array
    {
        return $this->functionCall('class_exists', [$class], $result);
    }

    protected function callFileGetContent(string $fileName, string $sourceFixture): array
    {
        return $this->functionCall('file_get_contents', [$fileName], $sourceFixture);
    }

    protected function generateResourcePath(string $path): string
    {
        return base_path("/vendor/ronasit/laravel-project-initializator/resources/{$path}");
    }

    protected function callFilePutContent(string $fileName, string $result, int $flags = 0): array
    {
        return $this->functionCall('file_put_contents', [$fileName, $result, $flags]);
    }

    protected function callShellExec(string $command, string $result = 'success'): array
    {
        return $this->functionCall('shell_exec', [$command], $result);
    }

    protected function changeEnvFileCall(string $fileName, string $sourceFixture, string $resultFixture): array
    {
        return [
            $this->functionCall('is_file', [$fileName]),
            $this->callFileGetContent($fileName, $this->getFixture($sourceFixture)),
            $this->callFilePutContent($fileName, $this->getFixture($resultFixture)),
        ];
    }

    protected function changeConfigFileCall(string $fileName, string $sourceFixture, string $resultFixture): array
    {
        return [
            $this->functionCall('file_exists', [$fileName]),
            $this->callFileGetContent($fileName, $this->getFixture($sourceFixture)),
            $this->callFilePutContent($fileName, $this->getFixture($resultFixture)),
        ];
    }
}
