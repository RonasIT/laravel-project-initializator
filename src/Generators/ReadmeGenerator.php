<?php

namespace RonasIT\ProjectInitializator\Generators;

use RonasIT\ProjectInitializator\DTO\ContactDTO;
use RonasIT\ProjectInitializator\DTO\ResourceDTO;

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

    protected array $methodsToCall = [];

    protected array $resources = [];
    protected array $contacts = [];

    public function setAppInfo(string $appName, string $appType, string $appUrl, string $codeOwnerEmail): void
    {
        $this->appName = $appName;
        $this->appType = $appType;
        $this->appUrl = $appUrl;
        $this->codeOwnerEmail = $codeOwnerEmail;
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

    public function getResource(string $key): ?ResourceDTO
    {
        foreach ($this->resources as $resource) {
            if ($resource->key === $key) {
                return $resource;
            }
        }

        return null;
    }

    public function getConfigurableContacts(): array
    {
        return $this->contacts = [
            'manager' => new ContactDTO('Manager'),
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
            callback: fn (ResourceDTO $resource) => $resource->isActive() && ($resource->localPath),
        );
    }

    public function addRenovate(): void
    {
        $this->methodsToCall[] = 'fillRenovate';
    }

    public function addResourcesAndContacts(): void
    {
        $this->methodsToCall[] = 'fillResourcesAndContacts';
    }

    public function addPrerequisites(): void
    {
        $this->methodsToCall[] = 'fillPrerequisites';
    }

    public function addGitProjectPath(string $path): void
    {
        $this->gitProjectPath = $path;
    }

    public function addGettingStarted(): void
    {
        $this->methodsToCall[] = 'fillGettingStarted';
    }

    public function addEnvironments(): void
    {
        $this->methodsToCall[] = 'fillEnvironments';
    }

    public function addCredentialsAndAccess(): void
    {
        $this->methodsToCall[] = 'fillCredentialsAndAccess';
    }

    public function addClerkAuthType(): void
    {
        $this->methodsToCall[] = 'fillClerkAuthType';
    }

    public function save(): void
    {
        $this->prepareReadme();

        foreach ($this->methodsToCall as $part) {
            $this->$part();
        }

        $this->saveReadme();
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

        foreach ($this->resources as $resource) {
            if ($resource->getLink() === 'later') {
                $this->setReadmeValue($filePart, "{$resource->key}_link");
                $this->setReadmeValue($filePart, "{$resource->key}_later", self::LATER_TEXT);
            } elseif ($resource->isActive()) {
                $this->setReadmeValue($filePart, "{$resource->key}_link", $resource->getLink());
                $this->setReadmeValue($filePart, "{$resource->key}_later");
            }

            $this->removeTag($filePart, $resource->key, !$resource->isActive());
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
            $email = $resource->getEmail();

            if (!empty($email)) {
                $this->setReadmeValue($filePart, "{$resource->key}_email", $email);
                $this->setReadmeValue($filePart, "{$resource->key}_password", $resource->getPassword());
            }

            $this->removeTag($filePart, "{$resource->key}_credentials", empty($email));
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
}
