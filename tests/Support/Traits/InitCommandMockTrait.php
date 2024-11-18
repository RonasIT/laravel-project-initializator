<?php

namespace RonasIT\Support\Tests\Support\Traits;

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

//        $this->mockNativeFunction(
//            namespace: 'RonasIT\Support\Console\Commands',
//            callChain: [
//                [
//                    'function' => 'file_get_contents',
//                    'arguments' => ['.env.example'],
//                    'result' => $this->getFixture('env.example.yml'),
//                ],
//                [
//                    'function' => 'file_get_contents',
//                    'arguments' => ['.env.development'],
//                    'result' => $this->getFixture('env.development.yml'),
//                ]
//            ]
//        );

        $this->mockNativeFunction(
            namespace: 'RonasIT\Support\Commands',
            callChain: array_map(
                fn ($call) => $this->functionCall('file_put_contents', $call),
                $callChain,
            )
        );
    }

    public function mockShellExec(): void
    {
        $this->mockNativeFunction('RonasIT\Support\Commands', [
            $this->functionCall(
                name: 'shell_exec',
                arguments: ['git ls-remote --get-url origin'],
                result: 'https://github.com/ronasit/laravel-helpers.git'
            ),
        ]);
    }
}
