<?php

namespace RonasIT\ProjectInitializator\Support;

use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Process\Process as SymfonyProcess;

class ShellExecutor
{
    public function composerRequire(string $package, bool $dev = false): void
    {
        $devFlag = ($dev) ? '--dev ' : '';

        $this->run("composer require {$devFlag}{$package} --ansi");
    }

    public function composerRemove(string $package, bool $dev = false): void
    {
        $devFlag = ($dev) ? '--dev ' : '';

        $this->run("composer remove {$devFlag}{$package} --ansi");
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

    protected function run(string $command): ProcessResult
    {
        return Process::forever()->run($command, function (string $type, string $output) {
            if ($type === SymfonyProcess::ERR) {
                fwrite(STDERR, $output);
            }
        });
    }
}
