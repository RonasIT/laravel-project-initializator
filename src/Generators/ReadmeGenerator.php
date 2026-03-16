<?php

namespace RonasIT\ProjectInitializator\Generators;

use RonasIT\ProjectInitializator\DTO\ResourceDTO;
use RonasIT\ProjectInitializator\Enums\ReadmeBlockEnum;

class ReadmeGenerator
{
    protected string $readmeContent = '';

    protected string $appName;
    protected string $appType;
    protected string $appUrl;
    protected string $codeOwnerEmail;
    protected string $managerEmail = ':manager_link';
    protected string $gitProjectPath;

    protected array $resources = [];
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

    public function setAppInfo(string $appName, string $appType, string $appUrl, string $codeOwnerEmail): self
    {
        $this->appName = $appName;
        $this->appType = $appType;
        $this->appUrl = $appUrl;
        $this->codeOwnerEmail = $codeOwnerEmail;

        return $this;
    }

    public function setManagerEmail(string $email): void
    {
        $this->managerEmail = $email;
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

        if (!empty($this->resources)) {
            $this->addBlock(ReadmeBlockEnum::ResourcesAndContacts);
        }

        foreach ($this->blocksMethodsMap as $block => $method) {
            if (in_array($block, $this->enabledBlocks)) {
                $this->$method();
            }
        }

        file_put_contents('README.md', $this->readmeContent);
    }

    protected function fillProjectInfo(): void
    {
        $this->readmeContent = view('initializator::readme.readme_head', [
            'projectName' => $this->appName,
            'appType' => $this->appType,
        ])->render();
    }

    protected function fillResourcesAndContacts(): void
    {
        $this->addContent('resources_and_contacts');

        $this->addContent('resources', [
            'resources' => $this->resources,
            'apiLink' => $this->appUrl,
        ]);

        $this->addContent('contacts', [
            'manager' => $this->managerEmail,
            'teamLead' => $this->codeOwnerEmail,
        ]);
    }

    protected function fillPrerequisites(): void
    {
        $this->addContent('prerequisites');
    }

    protected function fillGettingStarted(): void
    {
        $projectDirectory = basename($this->gitProjectPath, '.git');

        $this->addContent('getting_started', [
            'gitProjectPath' => $this->gitProjectPath,
            'projectDirectory' => $projectDirectory,
        ]);
    }

    protected function fillEnvironments(): void
    {
        $this->addContent('environments', [
            'apiLink' => $this->appUrl,
        ]);
    }

    protected function fillCredentialsAndAccess(): void
    {
        $this->addContent('credentials_and_access', [
            'credentials' => $this->resources,
        ]);
    }

    protected function fillClerk(): void
    {
        $this->addContent('clerk');
    }

    protected function fillRenovate(): void
    {
        $this->addContent('renovate');
    }

    protected function addContent(string $view, array $data = []): void
    {
        $content = view("initializator::readme.{$view}", $data)->render();

        $this->readmeContent .= "\n{$content}";
    }
}
