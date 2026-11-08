<?php

namespace RonasIT\ProjectInitializator\Support;

use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Process\Process as SymfonyProcess;

class ShellExecutor
{
    public function composerRequire(string $package, bool $dev = false): void
    {
        $this->composer('require', $package, $dev);
    }

    public function composerRemove(string $package, bool $dev = false): void
    {
        $this->composer('remove', $package, $dev);
    }

    public function artisan(string $command): void
    {
        $this->run("php artisan {$command} --ansi");
    }

    public function getGitRemoteUrl(): string
    {
        $result = $this->run('git ls-remote --get-url origin');

        return ($result->successful()) ? trim($result->output()) : '';
    }

    protected function composer(string $action, string $package, bool $dev): void
    {
        $devFlag = ($dev) ? '--dev ' : '';

        $this->run("composer {$action} {$devFlag}{$package} --ansi");
    }

    protected function run(string $command): ProcessResult
    {
        return Process::forever()->run($command, function (string $type, string $output) {
            if ($type === SymfonyProcess::ERR) {
                fwrite(STDERR, $output);
            }
        });
    }
}
