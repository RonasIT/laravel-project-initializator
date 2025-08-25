<?php

namespace RonasIT\ProjectInitializator\Tests\Support\Traits;

use Illuminate\Support\Arr;
use RonasIT\Support\Traits\MockTrait;

trait InitCommandMockTrait
{
    use MockTrait;

    // public function mockFilePutContent(...$arguments): void {
    //     $callChain = [
    //         ...$arguments,
    //     ];

    //     $this->mockNativeFunction(
    //         namespace: 'RonasIT\ProjectInitializator\Commands',
    //         callChain: array_map(
    //             fn ($call) => $this->functionCall('file_put_contents', $call),
    //             $callChain,
    //         ),
    //     );
    // }

    public function mockShellExec(array ...$rawCallChain): void
    {
        $callChain = array_map(fn ($call) => $this->functionCall(
            name: 'shell_exec',
            arguments: Arr::wrap($call['arguments']),
            result: Arr::get($call, 'result', 'success'),
        ), $rawCallChain);

        $this->mockNativeFunction('RonasIT\ProjectInitializator\Commands', $callChain);
    }

    // public function mockFileGetContent(array ...$rawCallChain): void
    // {
    //     $callChain = array_map(fn ($call) => $this->functionCall(
    //         name: 'file_get_contents',
    //         arguments: $call['arguments'],
    //         result: $call['result'],
    //     ), $rawCallChain);

    //     $this->mockNativeFunction(
    //         namespace: 'RonasIT\ProjectInitializator\Commands',
    //         callChain: $callChain,
    //     );
    // }

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

    public function mockIsFile(string $namespace, array $rawCallChain): void 
    {
        $callChain = array_map(fn ($path) => $this->functionCall(
            name: 'is_file',
            arguments: [$path],
            result: true,
        ), $rawCallChain);

        $this->mockNativeFunction(
            namespace: $namespace,
            callChain: $callChain,
        );
    }

    public function mockFileExists(string $namespace, array $rawCallChain): void 
    {
        $callChain = array_map(fn ($path) => $this->functionCall(
            name: 'file_exists',
            arguments: [$path],
            result: true,
        ), $rawCallChain);

        $this->mockNativeFunction(
            namespace: $namespace,
            callChain: $callChain,
        );
    }
    
    public function mockFileUpdate(string $namespace, array ...$rawChain): void 
    {
        $this->mockFileGetContent($namespace, $rawChain);
        $this->mockFilePutContent($namespace, $rawChain);
    }

    public function mockFileGetContent(string $namespace, array $rawCallChain): void 
    {
        $callChain = array_map(fn ($call) => $this->functionCall(
            name: 'file_get_contents',
            arguments: [$call['path']],
            result: $this->getFixture($call['source']),
        ), $rawCallChain);

        $this->mockNativeFunction(
            namespace: $namespace,
            callChain: $callChain,
        );
    }

    public function mockFilePutContent(string $namespace, array $rawCallChain): void 
    {
        $callChain = array_map(fn ($call) => $this->functionCall(
            name: 'file_put_contents',
            arguments: [$call['path'], $this->getFixture($call['result'])],
            result: 1,
        ), $rawCallChain);

        $this->mockNativeFunction(
            namespace: $namespace,
            callChain: $callChain,
        );
    }
}
