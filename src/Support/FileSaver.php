<?php

namespace RonasIT\ProjectInitializator\Support;

use Illuminate\Contracts\View\View;

class FileSaver
{
    public function publishClass(View $template, string $fileName, string $fileDirectory): void
    {
        $data = $template->render();

        $this->saveFile("$fileName.php", "<?php\n\n{$data}", $fileDirectory);
    }

    public function publishJSON(string $filePath, mixed $data, int $jsonFlags = JSON_PRETTY_PRINT): void
    {
        $json = json_encode($data, $jsonFlags) . "\n";

        $this->saveFile($filePath, $json);
    }

    public function saveFile(string $fileName, mixed $data, string $fileDirectory = ''): void
    {
        if (!empty($fileDirectory)) {
            if (!is_dir($fileDirectory)) {
                mkdir($fileDirectory, 0777, true);
            }

            $fileName = "{$fileDirectory}/{$fileName}";
        }

        file_put_contents($fileName, $data);
    }
}
