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

    protected function mockAdminDefaultPassword(string $resultPassword): array
    {
        return [
            $this->functionCall('substr', ['0058a062', 0, 8], $resultPassword),
            $this->functionCall('md5', ['0058a062'], '0058a062'),
            $this->functionCall('uniqid', [], '0058a062'),
        ];
    }
}
