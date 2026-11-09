<?php

namespace RonasIT\ProjectInitializator\Tests\Support\Traits;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;
use Mockery;
use RonasIT\Support\Traits\MockTrait;
use Throwable;

trait InitCommandMockTrait
{
    use EnvMockTrait;
    use MockTrait;

    protected function callClassExists(string $class, bool $result = true): array
    {
        return $this->functionCall('class_exists', [$class], $result);
    }

    protected function callFileGetContent(string $fileName, string $sourceFixture): array
    {
        return $this->functionCall('file_get_contents', [$fileName], $sourceFixture);
    }

    protected function callFilePutContent(string $fileName, string $result, int $flags = 0): array
    {
        return $this->functionCall('file_put_contents', [$fileName, $result, $flags]);
    }

    protected function callShellExec(string $command, string $result = 'success', int $exitCode = 0): array
    {
        return [
            'command' => $command,
            'result' => Process::result($result, exitCode: $exitCode),
        ];
    }

    protected function mockShellExec(array ...$calls): void
    {
        $executedCommands = [];
        $fakes = [];

        foreach ($calls as $call) {
            $fakes[$call['command']] = function (PendingProcess $process) use ($call, &$executedCommands) {
                $executedCommands[] = $process->command;

                return $call['result'];
            };
        }

        Process::preventStrayProcesses();
        Process::fake($fakes);

        $this->beforeApplicationDestroyed(function () use ($calls, &$executedCommands) {
            $this->assertSame(Arr::pluck($calls, 'command'), $executedCommands, 'Shell commands were not executed as expected.');
        });
    }

    protected function callGlob(string $pattern, array $result): array
    {
        return $this->functionCall('glob', [$pattern], $result);
    }

    protected function changeBootstrapAppCall(string $sourceFixture, string $resultFixture): array
    {
        return [
            $this->callFileGetContent('bootstrap/app.php', $this->getFixture("bootstrap/{$sourceFixture}")),
            $this->callFilePutContent('bootstrap/app.php', $this->getFixture("bootstrap/{$resultFixture}")),
        ];
    }

    protected function changeAppFileCall(string $fileName, string $sourceFixture, string $resultFixture): array
    {
        return [
            $this->callFileGetContent(app_path($fileName), $this->getFixture("{$sourceFixture}")),
            $this->callFilePutContent(app_path($fileName), $this->getFixture("{$resultFixture}")),
        ];
    }

    protected function changeConfigFileCall(string $fileName, string $sourceFixture, string $resultFixture): array
    {
        return [
            $this->callFileExists(base_path($fileName)),
            $this->callFileGetContent(base_path($fileName), $this->getFixture("configs/{$sourceFixture}")),
            $this->callFilePutContent(base_path($fileName), $this->getFixture("configs/{$resultFixture}")),
        ];
    }

    protected function mockMigrationFileWrite(string $fileName, string $fixture): array
    {
        return $this->callFilePutContent("database/migrations/{$fileName}", $this->getFixture("migrations/{$fixture}"));
    }

    protected function mockReadmeFileWrite(string $fixture): array
    {
        return $this->callFilePutContent('README.md', $this->getFixture("readme/{$fixture}"));
    }

    protected function mockArtisanMigrateCall(?Throwable $exception = null): void
    {
        $kernel = app(Kernel::class);

        $mock = Mockery::mock($kernel)->makePartial();

        $mock
            ->shouldReceive('call')
            ->with('migrate', ['--force' => true, '--ansi' => true])
            ->once()
            ->andReturnUsing(fn () => (empty($exception)) ? 0 : throw $exception);

        // As Kernel is final, the Artisan facade can not be mocked, so we need to create a partial mock from the resolved instance and swap the 'migrate' call via the Artisan facade.
        $mock
            ->shouldReceive('call')
            ->withAnyArgs()
            ->andReturnUsing(fn (...$args) => $kernel->call(...$args));

        Artisan::swap($mock);
    }
}
