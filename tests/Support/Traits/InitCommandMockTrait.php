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

    protected function callCopy(string $source, string $result): array
    {
        return $this->functionCall('copy', [$source, $result], true);
    }

    protected function callFileExists(string $fileName, bool $result = true): array
    {
        return $this->functionCall('file_exists', [$fileName], $result);
    }

    protected function callFileGetContent(string $fileName, string $sourceFixture): array
    {
        return $this->functionCall('file_get_contents', [$fileName], $sourceFixture);
    }

    protected function callFilePutContent(string $fileName, string $result, int $flags = 0): array
    {
        return $this->functionCall('file_put_contents', [$fileName, $result, $flags]);
    }

    protected function callShellExec(string $command, string $result = 'success'): array
    {
        return $this->functionCall('shell_exec', [$command], $result);
    }

    protected function callGlob(string $pattern, array $result): array
    {
        return $this->functionCall('glob', [$pattern], $result);
    }

    protected function copyEnvFileCall(string $envFile, string $sourceEnvFile = '.env.example'): array
    {
        return [
            $this->callFileExists($envFile, false),
            $this->callCopy($sourceEnvFile, $envFile),
        ];
    }

    protected function callRandomBytes(): array
    {
        return $this->functionCall('random_bytes', [32], base64_decode('RMlWJrRHIuHLYRplJvWaLNwWvRoVkQdDTZe6U1Z4xRY='));
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
            $this->callFileExists(base_path($fileName)),
            $this->callFileGetContent(base_path($fileName), $this->getFixture($sourceFixture)),
            $this->callFilePutContent(base_path($fileName), $this->getFixture($resultFixture)),
        ];
    }
}
