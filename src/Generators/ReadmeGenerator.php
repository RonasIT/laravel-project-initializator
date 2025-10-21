<?php

namespace RonasIT\ProjectInitializator\Generators;

use Illuminate\Support\Arr;

class ReadmeGenerator
{
    protected const string TEMPLATES_PATH = 'vendor/ronasit/laravel-project-initializator/resources/md/readme';

    protected string $readmeContent = '';

    public array $appInfo = [];

    public array $resourcesItems = [
        'issue_tracker' => [
            'title' => 'Issue Tracker',
        ],
        'figma' => [
            'title' => 'Figma',
        ],
        'sentry' => [
            'title' => 'Sentry',
        ],
        'datadog' => [
            'title' => 'DataDog',
        ],
        'argocd' => [
            'title' => 'ArgoCD',
        ],
        'telescope' => [
            'title' => 'Laravel Telescope',
            'default_url' => true,
        ],
        'nova' => [
            'title' => 'Laravel Nova',
            'default_url' => true,
        ],
    ];

    public array $contactsItems = [
        'manager' => [
            'title' => 'Manager',
        ],
    ];

    public array $credentialsItems = [
        'telescope' => [
            'title' => 'Laravel Telescope',
        ],
        'nova' => [
            'title' => 'Laravel Nova',
        ],
    ];

    protected function prepareReadme(): void
    {
        $file = $this->loadReadmePart('README.md');

        $this->setReadmeValue($file, 'project_name', $this->appInfo['name']);
        $this->setReadmeValue($file, 'type', $this->appInfo['type']);

        $this->readmeContent = $file;
    }

    protected function fillResourcesAndContacts(): void
    {
        $filePart = $this->loadReadmePart('RESOURCES_AND_CONTACTS.md');

        $this->updateReadmeFile($filePart);
    }

    protected function fillResources(): void
    {
        $filePart = $this->loadReadmePart('RESOURCES.md');
        $laterText = '(will be added later)';

        foreach ($this->resourcesItems as $key => $resource) {
            if ($resource['link'] === 'later') {
                $this->setReadmeValue($filePart, "{$key}_link");
                $this->setReadmeValue($filePart, "{$key}_later", $laterText);
            } elseif ($resource['link'] !== 'no') {
                $this->setReadmeValue($filePart, "{$key}_link", $resource['link']);
                $this->setReadmeValue($filePart, "{$key}_later");
            }

            $this->removeTag($filePart, $key, $resource['link'] === 'no');
        }

        $this->setReadmeValue($filePart, 'api_link', $this->appInfo['url']);
        $this->updateReadmeFile($filePart);
    }

    protected function fillContacts(): void
    {
        $filePart = $this->loadReadmePart('CONTACTS.md');

        foreach ($this->contactsItems as $key => $value) {
            if (Arr::has($value, 'email')) {
                $this->setReadmeValue($filePart, "{$key}_link", $value['email']);
            }

            $this->removeTag($filePart, $key);
        }

        $this->setReadmeValue($filePart, 'team_lead_link', $this->appInfo['code_owner_email']);

        $this->updateReadmeFile($filePart);
    }

    protected function fillPrerequisites(): void
    {
        $filePart = $this->loadReadmePart('PREREQUISITES.md');

        $this->updateReadmeFile($filePart);
    }

    protected function fillGettingStarted(): void
    {
        $gitProjectPath = trim((string) shell_exec('git ls-remote --get-url origin'));
        $projectDirectory = basename($gitProjectPath, '.git');
        $filePart = $this->loadReadmePart('GETTING_STARTED.md');

        $this->setReadmeValue($filePart, 'git_project_path', $gitProjectPath);
        $this->setReadmeValue($filePart, 'project_directory', $projectDirectory);

        $this->updateReadmeFile($filePart);
    }

    protected function fillEnvironments(): void
    {
        $filePart = $this->loadReadmePart('ENVIRONMENTS.md');

        $this->setReadmeValue($filePart, 'api_link', $this->appInfo['url']);
        $this->updateReadmeFile($filePart);
    }

    protected function fillCredentialsAndAccess(): void
    {
        $filePart = $this->loadReadmePart('CREDENTIALS_AND_ACCESS.md');

        foreach ($this->credentialsItems as $key => $item) {
            if (Arr::has($item, 'email')) {
                $this->setReadmeValue($filePart, "{$key}_email", $item['email']);
                $this->setReadmeValue($filePart, "{$key}_password", $item['password']);
                $this->removeTag($filePart, "{$key}_credentials");
            }

            $this->removeTag($filePart, "{$key}_credentials", true);
        }

        if (!Arr::has($this->credentialsItems, 'admin_credentials')) {
            $this->removeTag($filePart, 'admin_credentials', true);
        }
        $this->updateReadmeFile($filePart);
    }

    protected function fillClerkAuthType(): void
    {
        $filePart = $this->loadReadmePart('CLERK.md');

        $this->updateReadmeFile($filePart);
    }

    protected function fillRenovate(): void
    {
        $filePart = $this->loadReadmePart('RENOVATE.md');

        $this->updateReadmeFile($filePart);
    }

    protected function saveReadme(): void
    {
        file_put_contents('README.md', $this->readmeContent);
    }

    protected function loadReadmePart(string $fileName): string
    {
        $file = base_path(DIRECTORY_SEPARATOR . self::TEMPLATES_PATH . DIRECTORY_SEPARATOR . $fileName);

        return file_get_contents($file);
    }

    protected function setReadmeValue(string &$file, string $key, string $value = ''): void
    {
        $file = str_replace(":{$key}", $value, $file);
    }

    protected function updateReadmeFile(string $filePart): void
    {
        $filePart = preg_replace('#(\n){3,}#', "\n", $filePart);

        $this->readmeContent .= "\n" . $filePart;
    }

    protected function removeTag(string &$text, string $tag, bool $removeWholeString = false): void
    {
        $regex = ($removeWholeString)
            ? "#({{$tag}})(.|\s)*?({/{$tag}})#"
            : "# {0,1}{(/*){$tag}}#";

        $text = preg_replace($regex, '', $text);
    }

    public function generate(array $parts): void
    {
        $this->prepareReadme();

        foreach ($parts as $part) {
            if (method_exists($this, $part)) {
                $this->{$part}();
            }
        }

        $this->saveReadme();
    }
}
