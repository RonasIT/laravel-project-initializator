<?php

namespace RonasIT\ProjectInitializator\Tests\Support\Traits;

use Illuminate\Support\Arr;
use RonasIT\Support\Traits\MockTrait;

trait InitCommandMockTrait
{
    use MockTrait;

    public function mockFilePutContent(...$arguments): void
    {
        $callChain = [
            ['.env.example', $this->getFixture('env.example.yml'), 'optionalParameter', 'optionalParameter'],
            ['.env.development', $this->getFixture('env.development.yml'), 'optionalParameter', 'optionalParameter'],
            ...$arguments,
        ];

        $this->mockNativeFunction(
            namespace: 'RonasIT\ProjectInitializator\Commands',
            callChain: array_map(
                fn ($call) => $this->functionCall('file_put_contents', $call),
                $callChain,
            )
        );
    }

    public function mockShellExec(): void
    {
        $this->mockNativeFunction('RonasIT\ProjectInitializator\Commands', [
            $this->functionCall(
                name: 'shell_exec',
                arguments: ['git ls-remote --get-url origin'],
                result: 'https://github.com/ronasit/laravel-helpers.git',
            ),
        ]);
    }

    public function mockFileGetContent(...$rawCallChain): void
    {
        $callChain = array_map(fn ($call) => [
            'function' => 'file_get_contents',
            'arguments' => array_merge(
                Arr::get($call, 'arguments', []),
                ['optionalParameter', 'optionalParameter', 'optionalParameter', 'optionalParameter'],
            ),
            'result' => $call['result'],
        ], $rawCallChain);

        $this->mockNativeFunction(
            namespace: 'RonasIT\ProjectInitializator\Commands',
            callChain: $callChain,
        );
    }

    protected function getTemplate(string $template): string
    {
        return file_get_contents(dirname(__DIR__, 3) . "/resources/md/readme/{$template}");
    }
}
