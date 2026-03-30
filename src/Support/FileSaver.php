<?php

namespace RonasIT\ProjectInitializator\Support;

use Illuminate\Contracts\View\View;

class FileSaver
{
    public function publishClass(View $template, string $fileName, string $filePath): void
    {
        $fileName = "{$fileName}.php";

        if (!is_dir($filePath)) {
            mkdir($filePath, 0777, true);
        }

        $data = $template->render();

        $this->saveFile("{$filePath}/{$fileName}", "<?php\n\n{$data}");
    }

    public function publishJSON(string $fileName, mixed $data, int $jsonFlags): void
    {
        $json = json_encode($data, $jsonFlags) . "\n";

        $this->saveFile($fileName, $json);
    }

    public function saveFile(string $filename, mixed $data, int $flags = 0): void
    {
        file_put_contents($filename, $data, $flags);
    }
}
