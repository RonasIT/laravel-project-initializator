<?php

namespace RonasIT\ProjectInitializator\Tests;

use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use PHPUnit\Framework\Attributes\DataProvider;
use RonasIT\ProjectInitializator\Support\ShellExecutor;
use RonasIT\Support\Traits\MockTrait;

class ShellExecutorTest extends TestCase
{
    use MockTrait;

    public static function getComposerCommandsData(): array
    {
        return [
            [
                'method' => 'composerRequire',
                'dev' => false,
                'command' => 'composer require vendor/package --ansi',
            ],
            [
                'method' => 'composerRequire',
                'dev' => true,
                'command' => 'composer require --dev vendor/package --ansi',
            ],
            [
                'method' => 'composerRemove',
                'dev' => false,
                'command' => 'composer remove vendor/package --ansi',
            ],
            [
                'method' => 'composerRemove',
                'dev' => true,
                'command' => 'composer remove --dev vendor/package --ansi',
            ],
        ];
    }

    #[DataProvider('getComposerCommandsData')]
    public function testComposer(string $method, bool $dev, string $command): void
    {
        Process::preventStrayProcesses();
        Process::fake();

        app(ShellExecutor::class)->{$method}('vendor/package', $dev);

        $this->assertRanForever($command);
    }

    public function testArtisan(): void
    {
        Process::preventStrayProcesses();
        Process::fake();

        app(ShellExecutor::class)->artisan('migrate');

        $this->assertRanForever('php artisan migrate --ansi');
    }

    public static function getGitRemoteUrlData(): array
    {
        return [
            [
                'output' => "https://github.com/a/b.git\n",
                'exitCode' => 0,
                'result' => 'https://github.com/a/b.git',
            ],
            [
                'output' => 'fatal: not a git repository',
                'exitCode' => 1,
                'result' => '',
            ],
            [
                'output' => '',
                'exitCode' => 0,
                'result' => '',
            ],
        ];
    }

    #[DataProvider('getGitRemoteUrlData')]
    public function testGetGitRemoteUrl(string $output, int $exitCode, string $result): void
    {
        Process::preventStrayProcesses();
        Process::fake([
            'git ls-remote --get-url origin' => Process::result($output, exitCode: $exitCode),
        ]);

        $this->assertSame($result, app(ShellExecutor::class)->getGitRemoteUrl());
    }

    // A real process is used here: Process::fake() never invokes the output callback,
    // so the STDERR forwarding branch cannot be reached through a fake.
    public function testRunForwardsOnlyStderrToTerminal(): void
    {
        $this->mockNativeFunction(
            'RonasIT\ProjectInitializator\Support',
            $this->functionCall('fwrite', [STDERR, "error\n"]),
        );

        $executor = new class extends ShellExecutor
        {
            public function run(string $command): ProcessResult
            {
                return parent::run($command);
            }
        };

        $result = $executor->run('echo output; echo error >&2');

        $this->assertSame("output\n", $result->output());
        $this->assertSame("error\n", $result->errorOutput());
    }

    protected function assertRanForever(string $command): void
    {
        Process::assertRan(fn (PendingProcess $process) => $process->command === $command && $process->timeout === null);
    }
}
