<?php

namespace RonasIT\ProjectInitializator\Generators;

use RonasIT\ProjectInitializator\DTO\ContactDTO;
use RonasIT\ProjectInitializator\DTO\ResourceDTO;
use RonasIT\ProjectInitializator\Enums\ReadmeBlockEnum;

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
    protected array $enabledBlocks = [];

    protected array $blocksMethodsMap = [
        ReadmeBlockEnum::ResourcesAndContacts->value => 'fillResourcesAndContacts',
        ReadmeBlockEnum::Prerequisites->value => 'fillPrerequisites',
        ReadmeBlockEnum::GettingStarted->value => 'fillGettingStarted',
        ReadmeBlockEnum::Environments->value => 'fillEnvironments',
        ReadmeBlockEnum::CredentialsAndAccess->value => 'fillCredentialsAndAccess',
        ReadmeBlockEnum::Clerk->value => 'fillClerk',
        ReadmeBlockEnum::Renovate->value => 'fillRenovate',
    ];

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
            new ResourceDTO('issue_tracker', 'Issue Tracker', 'Here, you can report any issues or bugs related to the project.'),
            new ResourceDTO('figma', 'Figma', 'This is where we maintain all our design assets and mock-ups.'),
            new ResourceDTO('sentry', 'Sentry', 'To monitor application performance and error tracking.'),
            new ResourceDTO('telescope', 'Laravel Telescope', 'This is debug assistant for the Laravel framework.', 'telescope'),
            new ResourceDTO('nova', 'Laravel Nova', 'This is admin panel for the Laravel framework.', 'nova'),
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

    public function addBlock(ReadmeBlockEnum $block): void
    {
        $this->enabledBlocks[] = $block->value;
    }

    public function save(): void
    {
        $this->fillProjectInfo();

        if (!empty($this->resources) || !empty($this->contacts)) {
            $this->addBlock(ReadmeBlockEnum::ResourcesAndContacts);
        }

        foreach ($this->blocksMethodsMap as $block => $method) {
            if (in_array($block, $this->enabledBlocks)) {
                $this->$method();
            }
        }

        file_put_contents('README.md', $this->readmeContent);
    }

    public function fillProjectInfo(): void
    {
        $this->readmeContent = $this->renderBlade('readme', [
            'projectName' => $this->appName,
            'appType' => $this->appType,
        ]);
    }

    protected function fillResourcesAndContacts(): void
    {
        $filePart = $this->renderBlade('resources_and_contacts');

        $this->updateReadmeFile($filePart);

        $this->fillResources();

        $this->fillContacts();
    }

    protected function fillResources(): void
    {
        $resources = [];

        foreach ($this->resources as $resource) {
            $resources[$resource->key] = [
                'title' => $resource->title,
                'link' => $resource->link ?: null,
                'description' => $resource->description ?: null,
                'laterText' => empty($resource->link) && $resource->isActive ? ' ' . self::LATER_TEXT : null,
                'isActive' => $resource->isActive,
            ];
        }

        $filePart = $this->renderBlade('resources', [
            'resources' => $resources,
            'apiLink' => $this->appUrl,
        ]);

        $this->updateReadmeFile($filePart);
    }

    protected function fillContacts(): void
    {
        $contacts = [];

        foreach ($this->contacts as $contact) {
            if (!empty($contact->email)) {
                $contacts[$contact->key] = [
                    'email' => $contact->email,
                ];
            }
        }

        $filePart = $this->renderBlade('contacts', [
            'contacts' => $contacts,
            'teamLead' => $this->codeOwnerEmail,
        ]);

        $this->updateReadmeFile($filePart);
    }

    protected function fillPrerequisites(): void
    {
        $filePart = $this->renderBlade('prerequisites');

        $this->updateReadmeFile($filePart);
    }

    protected function fillGettingStarted(): void
    {
        $projectDirectory = basename($this->gitProjectPath, '.git');

        $filePart = $this->renderBlade('getting_started', [
            'gitProjectPath' => $this->gitProjectPath,
            'projectDirectory' => $projectDirectory,
        ]);

        $this->updateReadmeFile($filePart);
    }

    protected function fillEnvironments(): void
    {
        $filePart = $this->renderBlade('environments', [
            'apiLink' => $this->appUrl,
        ]);

        $this->updateReadmeFile($filePart);
    }

    protected function fillCredentialsAndAccess(): void
    {
        $credentials = [];

        foreach ($this->resources as $resource) {
            if (!empty($resource->email)) {
                $credentials[$resource->key] = [
                    'email' => $resource->email,
                    'password' => $resource->password,
                ];
            }
        }

        $filePart = $this->renderBlade('credentials_and_access', [
            'admin' => $credentials['admin'] ?? null,
            'telescope' => $credentials['telescope'] ?? null,
            'nova' => $credentials['nova'] ?? null,
        ]);

        $this->updateReadmeFile($filePart);
    }

    protected function fillClerk(): void
    {
        $filePart = $this->renderBlade('clerk');

        $this->updateReadmeFile($filePart);
    }

    protected function fillRenovate(): void
    {
        $filePart = $this->renderBlade('renovate');

        $this->updateReadmeFile($filePart);
    }

    protected function renderBlade(string $view, array $data = []): string
    {
        return view("initializator::readme.$view", $data)->render();
    }

    protected function updateReadmeFile(string $filePart): void
    {
        $this->readmeContent .= "\n" . $filePart;
    }
}
