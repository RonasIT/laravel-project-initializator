<?php

namespace RonasIT\ProjectInitializator\Support;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class FileSaver
{
    protected ?Carbon $lastMigrationTimestamp = null;

    public function __construct()
    {
        $this->lastMigrationTimestamp = Carbon::now();
    }

    public function publishClass(View $template, string $fileName, string $filePath): void
    {
        $fileName = "{$fileName}.php";

        if (!is_dir($filePath)) {
            mkdir($filePath, 0777, true);
        }

        $data = $template->render();

        $this->saveFile("{$filePath}/{$fileName}", "<?php\n\n{$data}");
    }

    public function publishMigration(View $view, string $migrationName): void
    {
        $time = $this->lastMigrationTimestamp->addSecond();

        $migrationName = "{$time->format('Y_m_d_His')}_{$migrationName}";

        $this->publishClass($view, $migrationName, 'database/migrations');
    }

    public function saveFile(string $filename, mixed $data, int $flags = 0): void
    {
        file_put_contents($filename, $data, $flags);
    }
}