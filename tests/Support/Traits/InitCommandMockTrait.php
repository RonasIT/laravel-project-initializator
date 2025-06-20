<?php

namespace RonasIT\ProjectInitializator\Tests\Support\Traits;

use Illuminate\Support\Arr;
use RonasIT\Support\Traits\MockTrait;

trait InitCommandMockTrait
{
    use MockTrait;

    public function mockFilePutContent(
        string $exampleEnvFixtureName = 'env.example.yml',
        string $developmentEnvFixtureName = 'env.development.yml',
        ...$arguments,
    ): void {
        $callChain = [
            ['.env.example', $this->getFixture($exampleEnvFixtureName)],
            ['.env.development', $this->getFixture($developmentEnvFixtureName)],
            ...$arguments,
        ];

        $this->mockNativeFunction(
            namespace: 'RonasIT\ProjectInitializator\Commands',
            callChain: array_map(
                fn ($call) => $this->functionCall('file_put_contents', $call),
                $callChain,
            ),
        );
    }

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
        return file_get_contents(base_path("/vendor/ronasit/laravel-project-initializator/resources/md/readme/{$template}"));
    }
}
