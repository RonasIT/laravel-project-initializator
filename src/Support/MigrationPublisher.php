<?php

namespace RonasIT\ProjectInitializator\Support;

use Illuminate\Support\Carbon;

class MigrationPublisher
{
    protected Carbon $lastMigrationTimestamp;

    public function __construct(
        protected FileSaver $fileSaver,
    ) {
        $this->lastMigrationTimestamp = Carbon::now();
    }

    public function publish(string $templateName, string $migrationName, mixed $data = []): void
    {
        $time = $this->lastMigrationTimestamp->addSecond();

        $migrationName = "{$time->format('Y_m_d_His')}_{$migrationName}";

        $view = view("initializator::migrations/{$templateName}")->with($data);

        $this->fileSaver->publishClass($view, $migrationName, 'database/migrations');
    }

    public function isMigrationExists(string $migrationName): bool
    {
        return !empty(glob(base_path("database/migrations/*_{$migrationName}.php")));
    }
}
