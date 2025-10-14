<?php

namespace RonasIT\ProjectInitializator\Generators;

class ReadmeGenerator
{
    public const string TEMPLATES_PATH = 'vendor/ronasit/laravel-project-initializator/resources/md/readme';

    protected string $readmeContent = '';

    public function fillReadme(string $appName, string $appType): void
    {
        $file = $this->loadReadmePart('README.md');

        $this->setReadmeValue($file, 'project_name', $appName);

        $this->setReadmeValue($file, 'type', $appType);

        $this->readmeContent = $file;
    }

    public function fillResourcesAndContacts(): void
    {
        $filePart = $this->loadReadmePart('RESOURCES_AND_CONTACTS.md');

        $this->updateReadmeFile($filePart);
    }

    public function fillPrerequisites(): void
    {
        $filePart = $this->loadReadmePart('PREREQUISITES.md');

        $this->updateReadmeFile($filePart);
    }


    public function fillEnvironments(string $appUrl): void
    {
        $filePart = $this->loadReadmePart('ENVIRONMENTS.md');

        $this->setReadmeValue($filePart, 'api_link', $appUrl);
        $this->updateReadmeFile($filePart);
    }

    public function fillClerkAuthType(): void
    {
        $filePart = $this->loadReadmePart('CLERK.md');

        $this->updateReadmeFile($filePart);
    }
    
    public function loadReadmePart(string $fileName): string
    {
        $file = base_path(DIRECTORY_SEPARATOR . self::TEMPLATES_PATH . DIRECTORY_SEPARATOR . $fileName);

        return file_get_contents($file);
    }

    public function updateReadmeFile(string $filePart): void
    {
        $filePart = preg_replace('#(\n){3,}#', "\n", $filePart);

        $this->readmeContent .= "\n" . $filePart;
    }

    public function removeTag(string &$text, string $tag, bool $removeWholeString = false): void
    {
        $regex = ($removeWholeString)
            ? "#({{$tag}})(.|\s)*?({/{$tag}})#"
            : "# {0,1}{(/*){$tag}}#";

        $text = preg_replace($regex, '', $text);
    }

    public function setReadmeValue(string &$file, string $key, string $value = ''): void
    {
        $file = str_replace(":{$key}", $value, $file);
    }

    public function saveReadme(): void
    {
        file_put_contents('README.md', $this->readmeContent);
    }

    public function fillRenovate(): void
    {
        $filePart = $this->loadReadmePart('RENOVATE.md');

        $this->updateReadmeFile($filePart);
    }
}
