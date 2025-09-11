<?php

namespace RonasIT\ProjectInitializator\Tests\Support\Traits;

use Illuminate\Support\Arr;
use RonasIT\Support\Traits\MockTrait;

trait InitCommandMockTrait
{
    use MockTrait;

    protected function callClassExists(string $class, bool $result = true): array
    {
        return [
            $this->functionCall('class_exists', [$class], $result),
        ];
    }

    protected function callFileGetContent(string $fileName, string $sourceFixture): array
    {
        return [
            $this->functionCall('file_get_contents', [base_path($fileName)], $this->getTemplate($sourceFixture)),
        ];
    }

    protected function getTemplate(string $template): string
    {
        return file_get_contents(base_path("/resources/md/readme/{$template}"));
    }

    protected function callFilePutContent(string $fileName, string $resultFixture): array
    {
        return [
            $this->functionCall('file_put_contents', [$fileName, $this->getFixture($resultFixture)]),
        ];
    }

    protected function callShellExec(string $command, string $result = 'success'): array
    {
        return [
            $this->functionCall('shell_exec', [$command], $result),
        ];
    }

    public function mockNamespaceFunctions(array ...$calls): void
    {
        $this->mockFunction('RonasIT\ProjectInitializator\Commands', ...$calls);
    } 

    public function mockLaravelConfigWriter(array ...$calls): void
    {
        $this->mockFunction('\Winter\LaravelConfigWriter', ...$calls);
    } 

    protected function changeEnvFileCall(string $fileName, string $sourceFixture, string $resultFixture): array
    {
        return [
            $this->functionCall('is_file', [$fileName]),
            $this->functionCall('file_get_contents', [$fileName], $this->getFixture($sourceFixture)),
            $this->functionCall('file_put_contents', [$fileName, $this->getFixture($resultFixture)]),
        ];
    }

    protected function changeConfigFileCall(string $fileName, string $sourceFixture, string $resultFixture): array
    {
        return [
            $this->functionCall('file_exists', [$fileName]),
            $this->functionCall('file_get_contents', [$fileName], $this->getFixture($sourceFixture)),
            $this->functionCall('file_put_contents', [$fileName, $this->getFixture($resultFixture)]),
        ];
    }
    
    protected function mockFunction(string $namespace, array ...$calls): void
    {
        $this->mockNativeFunction(
            namespace: $namespace,
            callChain: array_merge(...$calls),
        );
    }
}
