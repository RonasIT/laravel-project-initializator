<?php

namespace RonasIT\ProjectInitializator\Tests\Support\Traits;

use RonasIT\Support\Traits\MockTrait;

trait EnvMockTrait
{
    use MockTrait;

    protected function callFileExists(string $fileName, bool $result = true): array
    {
        return $this->functionCall('file_exists', [$fileName], $result);
    }

    protected function callCopy(string $source, string $result): array
    {
        return $this->functionCall('copy', [$source, $result], true);
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
            $this->functionCall('file_get_contents', [$fileName], $this->getFixture("env/{$sourceFixture}")),
            $this->functionCall('file_put_contents', [$fileName, $this->getFixture("env/{$resultFixture}"), 0]),
        ];
    }
}
