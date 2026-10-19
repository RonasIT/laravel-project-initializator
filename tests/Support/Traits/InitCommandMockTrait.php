<?php

namespace RonasIT\ProjectInitializator\Tests\Support\Traits;

use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use RonasIT\Support\Traits\MockTrait;

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

    protected function callShellExec(string $command, string $result = 'success'): array
    {
        return $this->functionCall('shell_exec', [$command], $result);
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

    protected function mockArtisanMigrateCall(int $exitCode = 0): void
    {
        // Migrations run in an external process, so the facade is faked instead of the Artisan kernel.
        // The handler asserts both the command and the DB settings passed as environment variables, and
        // the exit code lets a caller model a failing migration.
        Process::fake([
            '*' => function (PendingProcess $process) use ($exitCode) {
                $this->assertEquals('php artisan migrate --force --ansi', $process->command);

                $this->assertEquals(0, $process->timeout);

                $this->assertEquals([
                    'DB_CONNECTION' => 'pgsql',
                    'DB_HOST' => 'pgsql',
                    'DB_PORT' => '5432',
                    'DB_DATABASE' => 'postgres',
                    'DB_USERNAME' => 'postgres',
                    'DB_PASSWORD' => '',
                ], $process->environment);

                return Process::result(exitCode: $exitCode);
            },
        ]);
    }
}
