<?php

namespace RonasIT\ProjectInitializator\Tests\Support\Traits;

use Illuminate\Support\Arr;
use RonasIT\Support\Traits\MockTrait;

trait InitCommandMockTrait
{
    use MockTrait;

    public function mockShellExec(array ...$rawCallChain): void
    {
        $callChain = array_map(fn ($call) => $this->functionCall(
            name: 'shell_exec',
            arguments: Arr::wrap($call['arguments']),
            result: Arr::get($call, 'result', 'success'),
        ), $rawCallChain);

        $this->mockNativeFunction('RonasIT\ProjectInitializator\Commands', $callChain);
    }

    public function mockFileGetContent(array ...$rawCallChain): void
    {
        $callChain = array_map(fn ($call) => $this->functionCall(
            name: 'file_get_contents',
            arguments: $call['arguments'],
            result: $call['result'],
        ), $rawCallChain);

        $this->mockNativeFunction(
            namespace: 'RonasIT\ProjectInitializator\Commands',
            callChain: $callChain,
        );
    }

    public function mockFilePutContent(array ...$arguments): void 
    {
        $this->mockNativeFunction(
            namespace: 'RonasIT\ProjectInitializator\Commands',
            callChain: array_map(
                fn ($call) => $this->functionCall('file_put_contents', $call),
                $arguments,
            ),
        );
    }

    protected function mockClassExists(array ...$rawCallChain): void
    {
        $callChain = array_map(fn ($call) => $this->functionCall(
            name: 'class_exists',
            arguments: Arr::wrap($call['arguments']),
            result: Arr::get($call, 'result', true),
        ), $rawCallChain);

        $this->mockNativeFunction('RonasIT\ProjectInitializator\Commands', $callChain);
    }

    protected function getTemplate(string $template): string
    {
        return file_get_contents(base_path("/resources/md/readme/{$template}"));
    }

    public function mockLaravelConfigWriter(...$arguments): void
    {
        $this->mockLaravelConfigWriterCheckFile($arguments);
        $this->mockLaravelConfigWriterFileGetContent($arguments);
        $this->mockLaravelConfigWriterFilePutContent($arguments);
    }

    public function mockLaravelConfigWriterCheckFile(array $arguments): void
    {
        $this->mockNativeFunction(
            namespace: '\Winter\LaravelConfigWriter',
            callChain: array_map(
                fn ($call) => $this->functionCall($call['function'], [$call['path']], true),
                $arguments,
            ),
        );
    }

    public function mockLaravelConfigWriterFileGetContent(array $rawCallChain): void
    {
        $callChain = array_map(fn ($call) => $this->functionCall(
            name: 'file_get_contents',
            arguments: [$call['path']],
            result: $call['source'],
        ), $rawCallChain);

        $this->mockNativeFunction(
            namespace: '\Winter\LaravelConfigWriter',
            callChain: $callChain,
        );
    }

    public function mockLaravelConfigWriterFilePutContent(array $rawCallChain): void
    {
        $callChain = array_map(fn ($call) => $this->functionCall(
            name: 'file_put_contents',
            arguments: [$call['path'], $call['result']],
            result: 1,
        ), $rawCallChain);

        $this->mockNativeFunction(
            namespace: '\Winter\LaravelConfigWriter',
            callChain: $callChain,
        );
    }
}
