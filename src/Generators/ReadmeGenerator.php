<?php

namespace RonasIT\ProjectInitializator\Generators;

use Illuminate\Support\Arr;
use RonasIT\ProjectInitializator\DTO\ContactDTO;
use RonasIT\ProjectInitializator\DTO\ResourceDTO;

class ReadmeGenerator
{
    protected const string TEMPLATES_PATH = 'vendor/ronasit/laravel-project-initializator/resources/md/readme';

    protected string $readmeContent = '';

    protected string $appName;
    protected string $appType;
    protected string $appUrl;
    protected string $codeOwnerEmail;

    protected array $enabledParts = [];

    protected array $resources = [];

    protected array $contacts = [];

    public array $credentialsItems = [
        'telescope' => [
            'title' => 'Laravel Telescope',
        ],
        'nova' => [
            'title' => 'Laravel Nova',
        ],
    ];

    public function __construct()
    {
        $this->resources = [
            'issue_tracker' => new ResourceDTO('Issue Tracker'),
            'figma' => new ResourceDTO('Figma'),
            'sentry' => new ResourceDTO('Sentry'),
            'datadog' => new ResourceDTO('DataDog'),
            'argocd' => new ResourceDTO('ArgoCD'),
            'telescope' => new ResourceDTO('Laravel Telescope', true),
            'nova' => new ResourceDTO('Laravel Nova', true),
        ];
        
        $this->contacts = [
            'manager' => new ContactDTO('Manager'),
        ];
    }

    protected function prepareReadme(): void
    {
        $file = $this->loadReadmePart('README.md');

        $this->setReadmeValue($file, 'project_name', $this->appName);
        $this->setReadmeValue($file, 'type', $this->appType);

        $this->readmeContent = $file;
    }

    protected function fillResourcesAndContacts(): void
    {
        $filePart = $this->loadReadmePart('RESOURCES_AND_CONTACTS.md');

        $this->updateReadmeFile($filePart);

        $this->fillResources();

        $this->fillContacts();
    }

    protected function fillResources(): void
    {
        $filePart = $this->loadReadmePart('RESOURCES.md');
        $laterText = '(will be added later)';

        foreach ($this->resources as $key => $resource) {
            if ($resource->getLink() === 'later') {
                $this->setReadmeValue($filePart, "{$key}_link");
                $this->setReadmeValue($filePart, "{$key}_later", $laterText);
            } elseif ($resource->isActive()) {
                $this->setReadmeValue($filePart, "{$key}_link", $resource->getLink());
                $this->setReadmeValue($filePart, "{$key}_later");
            }

            $this->removeTag($filePart, $key, !$resource->isActive());
        }

        $this->setReadmeValue($filePart, 'api_link', $this->appUrl);
        $this->updateReadmeFile($filePart);
    }

    protected function fillContacts(): void
    {
        $filePart = $this->loadReadmePart('CONTACTS.md');

        foreach ($this->contacts as $key => $contact) {
            $email = $contact->getEmail();

            if (!empty($email)) {
                $this->setReadmeValue($filePart, "{$key}_link", $email);
            }

            $this->removeTag($filePart, $key);
        }

        $this->setReadmeValue($filePart, 'team_lead_link', $this->codeOwnerEmail);

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

        $this->setReadmeValue($filePart, 'api_link', $this->appUrl);
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
            } else {
                $this->removeTag($filePart, "{$key}_credentials", true);
            }
        }

        if (!Arr::has($this->credentialsItems, 'admin')) {
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
        $file = base_path(self::TEMPLATES_PATH . DIRECTORY_SEPARATOR . $fileName);

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

    public function setAppInfo(string $appName, string $appType, string $appUrl, string $codeOwnerEmail): void
    {
        $this->appName = $appName;
        $this->appType = $appType;
        $this->appUrl = $appUrl;
        $this->codeOwnerEmail = $codeOwnerEmail;
    }

    public function addAdmin(string $email, string $password): void
    {
        $this->credentialsItems['admin'] = [
            'title' => 'Default admin',
            'email' => $email,
            'password' => $password,
        ];
    }

    public function getConfigurableResources(): array
    {
        return $this->resources;
    }

    public function getResource(string $key): ?ResourceDTO
    {
        return $this->resources[$key] ?? null;
    }

    public function getConfigurableContacts(): array
    {
        return array_values($this->contacts);
    }

    public function addRenovate(): void
    {
        $this->enabledParts[] = [$this, 'fillRenovate'];
    }

    public function addResourcesAndContacts(): void
    {
        $this->enabledParts[] = [$this, 'fillResourcesAndContacts'];
    }

    public function addPrerequisites(): void
    {
        $this->enabledParts[] = [$this, 'fillPrerequisites'];
    }

    public function addGettingStarted(): void
    {
        $this->enabledParts[] = [$this, 'fillGettingStarted'];
    }

    public function addEnvironments(): void
    {
        $this->enabledParts[] = [$this, 'fillEnvironments'];
    }

    public function addCredentialsAndAccess(): void
    {
        $this->enabledParts[] = [$this, 'fillCredentialsAndAccess'];
    }

    public function addClerkAuthType(): void
    {
        $this->enabledParts[] = [$this, 'fillClerkAuthType'];
    }

    public function save(): void
    {
        $this->prepareReadme();

        foreach ($this->enabledParts as $part) {
            $part();
        }

        $this->saveReadme();
    }
}
