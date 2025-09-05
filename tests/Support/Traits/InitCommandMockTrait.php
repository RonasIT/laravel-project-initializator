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

    public function mockIsFile(array ...$arguments): void
    {
        $callChain = [
            [
                'function' => 'is_file',
                'arguments' => '.env.example',
            ],
            [
                'function' => 'is_file',
                'arguments' => '.env.development',
            ],
            ...$arguments,
        ];

        $this->mockNativeFunction(
            namespace: '\Winter\LaravelConfigWriter',
            callChain: array_map(
                fn ($call) => $this->functionCall($call['function'], [$call['arguments']], true),
                $callChain,
            ),
        );
    }

    public function mockFilesExist(array ...$arguments): void
    {
        $callChain = [
            [
                'function' => 'file_exists',
                'arguments' => base_path('config/auto-doc.php'),
            ],
            ...$arguments,
        ];

        $this->mockNativeFunction(
            namespace: '\Winter\LaravelConfigWriter',
            callChain: array_map(
                fn ($call) => $this->functionCall($call['function'], [$call['arguments']], true),
                $callChain,
            ),
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
            result: $call['source'],
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
            arguments: [$call['path'], $call['result']],
            result: 1,
        ), $rawCallChain);

        $this->mockNativeFunction(
            namespace: $namespace,
            callChain: $callChain,
        );
    }
}
