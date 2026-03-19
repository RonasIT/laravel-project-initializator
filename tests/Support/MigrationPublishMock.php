<?php

namespace RonasIT\ProjectInitializator\Tests\Support;

use Closure;
use Illuminate\Support\Carbon;

class MigrationPublishMock
{
    private array $paths = [];

    public function __construct(
        private readonly Carbon $timestamp,
        private readonly Closure $callFilePutContent,
        private readonly Closure $getFixture,
    ) {
    }

    public function __invoke(string $migrationName, string $fixture): array
    {
        $this->timestamp->addSecond();

        $path = $this->generatePath($migrationName);

        $this->paths[$migrationName] = $path;

        return ($this->callFilePutContent)($path, ($this->getFixture)($fixture));
    }

    public function getPath(string $migrationName): string
    {
        return $this->paths[$migrationName];
    }

    private function generatePath(string $migrationName): string
    {
        return "database/migrations/{$this->timestamp->format('Y_m_d_His')}_{$migrationName}.php";
    }
}
