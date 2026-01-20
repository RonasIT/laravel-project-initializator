<?php

namespace RonasIT\ProjectInitializator\Generators;

use RonasIT\ProjectInitializator\DTO\ContactDTO;
use RonasIT\ProjectInitializator\DTO\ResourceDTO;
use RonasIT\ProjectInitializator\Enums\ReadmePartEnum;

class ReadmeGenerator
{
    protected const string TEMPLATES_PATH = 'vendor/ronasit/laravel-project-initializator/resources/md/readme';
    protected const string LATER_TEXT = '(will be added later)';

    protected string $readmeContent = '';

    protected string $appName;
    protected string $appType;
    protected string $appUrl;
    protected string $codeOwnerEmail;
    protected string $gitProjectPath;

    protected array $resources = [];
    protected array $contacts = [];

    protected array $readmeParts = [
        ReadmePartEnum::ResourcesAndContacts->value => false,
        ReadmePartEnum::Prerequisites->value => false,
        ReadmePartEnum::GettingStarted->value => false,
        ReadmePartEnum::Environments->value => false,
        ReadmePartEnum::CredentialsAndAccess->value => false,
        ReadmePartEnum::ClerkAuthType->value => false,
        ReadmePartEnum::Renovate->value => false,
    ];

    public function __construct()
    {
        $this->readmeContent = $this->loadReadmePart('README.md');
    }

    public function setAppInfo(string $appName, string $appType, string $appUrl, string $codeOwnerEmail): void
    {
        $this->appName = $appName;
        $this->appType = $appType;
        $this->appUrl = $appUrl;
        $this->codeOwnerEmail = $codeOwnerEmail;
    }

    public function setGitProjectPath(string $path): void
    {
        $this->gitProjectPath = $path;
    }

    public function getConfigurableResources(): array
    {
        return [
            new ResourceDTO('issue_tracker', 'Issue Tracker'),
            new ResourceDTO('figma', 'Figma'),
            new ResourceDTO('sentry', 'Sentry'),
            new ResourceDTO('datadog', 'DataDog'),
            new ResourceDTO('argocd', 'ArgoCD'),
            new ResourceDTO('telescope', 'Laravel Telescope', 'telescope'),
            new ResourceDTO('nova', 'Laravel Nova', 'nova'),
        ];
    }

    public function addResource(ResourceDTO $resource): void
    {
        $this->resources[] = $resource;
    }

    public function getConfigurableContacts(): array
    {
        return [
            new ContactDTO('manager', 'Manager'),
        ];
    }

    public function addContact(ContactDTO $contact): void
    {
        $this->contacts[] = $contact;
    }

    public function getAccessRequiredResources(): array
    {
        return array_filter(
            array: $this->resources,
            callback: fn (ResourceDTO $resource) => $resource->isActive && $resource->localPath,
        );
    }

    public function enableReadmePart(ReadmePartEnum $part): void
    {
        $this->readmeParts[$part->value] = true;
    }

    public function save(): void
    {
        $this->fillProjectInfo();

        foreach ($this->readmeParts as $part => $enabled) {
            if ($enabled) {
                $this->$part();
            }
        }

        file_put_contents('README.md', $this->readmeContent);
    }

    protected function fillProjectInfo(): void
    {
        $this->setReadmeValue($this->readmeContent, 'project_name', $this->appName);
        $this->setReadmeValue($this->readmeContent, 'type', $this->appType);
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

        foreach ($this->resources as $resource) {
            if (empty($resource->link) && $resource->isActive) {
                $this->setReadmeValue($filePart, "{$resource->key}_link");
                $this->setReadmeValue($filePart, "{$resource->key}_later", self::LATER_TEXT);
            } elseif (!empty($resource->link)) {
                $this->setReadmeValue($filePart, "{$resource->key}_link", $resource->link);
                $this->setReadmeValue($filePart, "{$resource->key}_later");
            }

            $this->removeTag($filePart, $resource->key, !$resource->isActive);
        }

        $this->setReadmeValue($filePart, 'api_link', $this->appUrl);
        $this->updateReadmeFile($filePart);
    }

    protected function fillContacts(): void
    {
        $filePart = $this->loadReadmePart('CONTACTS.md');

        foreach ($this->contacts as $contact) {
            $email = $contact->email;

            if (!empty($email)) {
                $this->setReadmeValue($filePart, "{$contact->key}_link", $email);
            }

            $this->removeTag($filePart, $contact->key);
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
        $projectDirectory = basename($this->gitProjectPath, '.git');
        $filePart = $this->loadReadmePart('GETTING_STARTED.md');

        $this->setReadmeValue($filePart, 'git_project_path', $this->gitProjectPath);
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

        foreach ($this->resources as $resource) {
            if (!empty($resource->email)) {
                $this->setReadmeValue($filePart, "{$resource->key}_email", $resource->email);
                $this->setReadmeValue($filePart, "{$resource->key}_password", $resource->password);
            }

            $this->removeTag($filePart, "{$resource->key}_credentials", empty($resource->email));
        }

        if (!$this->getResource('admin')) {
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

    protected function getResource(string $key): ?ResourceDTO
    {
        return array_find($this->resources, fn (ResourceDTO $resource) => $resource->key === $key);
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
}
