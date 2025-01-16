<?php

namespace RonasIT\ProjectInitializator\Tests;

use Illuminate\Support\Facades\Artisan;
use RonasIT\ProjectInitializator\Tests\Support\TelescopeInstallCommand;
use RonasIT\ProjectInitializator\Tests\Support\Traits\InitCommandMockTrait;

class InitCommandTest extends TestCase
{
    use InitCommandMockTrait;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::registerCommand(new TelescopeInstallCommand());
    }

    public function testRunWithoutAdminAndReadmeCreation()
    {
        $this->mockFileGetContent(
            [
                'arguments' => ['.env.example'],
                'result' => $this->getFixture('env.example.yml'),
            ],
            [
                'arguments' => ['.env.development'],
                'result' => $this->getFixture('env.development.yml'),
            ],
        );

        $this->mockFilePutContent();

        $this->mockShellExec(
            ['arguments' => 'composer require ronasit/laravel-helpers --ansi'],
            ['arguments' => 'composer require ronasit/laravel-swagger --ansi'],
            ['arguments' => 'composer require --dev ronasit/laravel-entity-generator --ansi'],
            ['arguments' => 'composer require laravel/telescope --ansi'],
        );

        $this
            ->artisan('init "My App"')
            ->expectsOutput('Project initialized successfully!')
            ->expectsQuestion('Please enter an application URL', 'https://mysite.com')
            ->expectsConfirmation('Do you want to generate an admin user?')
            ->expectsConfirmation('Do you want to generate a README file?')
            ->expectsConfirmation('Do you want to install media package?')
            ->expectsConfirmation('Do you want to uninstall project-initializator package?')
            ->assertExitCode(0);
    }

    public function testRunWithAdminAndWithoutReadmeCreation()
    {
        $this->mockFileGetContent(
            [
                'arguments' => ['.env.example'],
                'result' => $this->getFixture('env.example.yml'),
            ],
            [
                'arguments' => ['.env.development'],
                'result' => $this->getFixture('env.development.yml'),
            ],
        );

        $this->mockFilePutContent(
            [
                'database/migrations/2018_11_11_111111_add_default_user.php',
                $this->getFixture('migration.php'),
                'optionalParameter',
                'optionalParameter',
            ]
        );

        $this->mockShellExec(
            ['arguments' => 'composer require ronasit/laravel-helpers --ansi'],
            ['arguments' => 'composer require ronasit/laravel-swagger --ansi'],
            ['arguments' => 'composer require --dev ronasit/laravel-entity-generator --ansi'],
            ['arguments' => 'composer require laravel/telescope --ansi'],
        );

        $this
            ->artisan('init "My App"')
            ->expectsOutput('Project initialized successfully!')
            ->expectsQuestion('Please enter an application URL', 'https://mysite.com')
            ->expectsConfirmation('Do you want to generate an admin user?', 'yes')
            ->expectsQuestion('Please enter an admin name', 'TestAdmin')
            ->expectsQuestion('Please enter an admin email', 'mail@mail.com')
            ->expectsQuestion('Please enter an admin password', '123456')
            ->expectsQuestion('Please enter an admin role id', 1)
            ->expectsConfirmation('Do you want to generate a README file?')
            ->expectsConfirmation('Do you want to install media package?')
            ->expectsConfirmation('Do you want to uninstall project-initializator package?')
            ->assertExitCode(0);
    }

    public function testRunWithAdminAndDefaultReadmeCreation()
    {
        $this->mockFileGetContent(
            [
                'arguments' => ['.env.example'],
                'result' => $this->getFixture('env.example.yml'),
            ],
            [
                'arguments' => ['.env.development'],
                'result' => $this->getFixture('env.development.yml'),
            ],
            [
                'arguments' => ['/app/resources/md/readme/README.md'],
                'result' => $this->getTemplate('README.md'),
            ],
            [
                'arguments' => ['/app/resources/md/readme/RESOURCES_AND_CONTACTS.md'],
                'result' => $this->getTemplate('RESOURCES_AND_CONTACTS.md'),
            ],
            [
                'arguments' => ['/app/resources/md/readme/RESOURCES.md'],
                'result' => $this->getTemplate('RESOURCES.md'),
            ],
            [
                'arguments' => ['/app/resources/md/readme/CONTACTS.md'],
                'result' => $this->getTemplate('CONTACTS.md'),
            ],
            [
                'arguments' => ['/app/resources/md/readme/PREREQUISITES.md'],
                'result' => $this->getTemplate('PREREQUISITES.md'),
            ],
            [
                'arguments' => ['/app/resources/md/readme/GETTING_STARTED.md'],
                'result' => $this->getTemplate('GETTING_STARTED.md'),
            ],
            [
                'arguments' => ['/app/resources/md/readme/ENVIRONMENTS.md'],
                'result' => $this->getTemplate('ENVIRONMENTS.md'),
            ],
            [
                'arguments' => ['/app/resources/md/readme/CREDENTIALS_AND_ACCESS.md'],
                'result' => $this->getTemplate('CREDENTIALS_AND_ACCESS.md'),
            ],
        );

        $this->mockFilePutContent(
            [
                'database/migrations/2018_11_11_111111_add_default_user.php',
                $this->getFixture('migration.php'),
                'optionalParameter',
                'optionalParameter',
            ],
            [
                'README.md',
                $this->getFixture('default_readme.md'),
                'optionalParameter',
                'optionalParameter',
            ]
        );

        $this->mockShellExec(
            ['arguments' => 'git ls-remote --get-url origin', 'result' => 'https://github.com/ronasit/laravel-helpers.git'],
            ['arguments' => 'composer require ronasit/laravel-helpers --ansi'],
            ['arguments' => 'composer require ronasit/laravel-swagger --ansi'],
            ['arguments' => 'composer require --dev ronasit/laravel-entity-generator --ansi'],
            ['arguments' => 'composer require laravel/telescope --ansi'],
        );

        $this
            ->artisan('init "My App"')
            ->expectsOutput('Project initialized successfully!')
            ->expectsQuestion('Please enter an application URL', 'https://mysite.com')
            ->expectsConfirmation('Do you want to generate an admin user?', 'yes')
            ->expectsQuestion('Please enter an admin name', 'TestAdmin')
            ->expectsQuestion('Please enter an admin email', 'mail@mail.com')
            ->expectsQuestion('Please enter an admin password', '123456')
            ->expectsQuestion('Please enter an admin role id', 1)
            ->expectsConfirmation('Do you want to generate a README file?', 'yes')
            ->expectsQuestion('What type of application will your API serve?', 'Multiplatform')
            ->expectsConfirmation('Do you need a `Resources & Contacts` part?', 'yes')
            ->expectsQuestion(
                'Are you going to use Issue Tracker? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'later'
            )
            ->expectsQuestion(
                'Are you going to use Figma? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'later'
            )
            ->expectsQuestion(
                'Are you going to use Sentry? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'later'
            )
            ->expectsQuestion(
                'Are you going to use DataDog? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'later'
            )
            ->expectsQuestion(
                'Are you going to use ArgoCD? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'later'
            )
            ->expectsQuestion(
                'Are you going to use Laravel Telescope? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'later'
            )
            ->expectsQuestion(
                'Are you going to use Laravel Nova? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'later'
            )
            ->expectsQuestion('Please enter a Manager\'s email', '')
            ->expectsQuestion('Please enter a Code Owner/Team Lead\'s email', '')
            ->expectsConfirmation('Do you need a `Prerequisites` part?', 'yes')
            ->expectsConfirmation('Do you need a `Getting Started` part?', 'yes')
            ->expectsConfirmation('Do you need an `Environments` part?', 'yes')
            ->expectsConfirmation('Do you need a `Credentials and Access` part?', 'yes')
            ->expectsConfirmation('Is Laravel Telescope\'s admin the same as default one?', 'yes')
            ->expectsConfirmation('Is Laravel Nova\'s admin the same as default one?', 'yes')
            ->expectsOutput('README generated successfully!')
            ->expectsOutput('Don`t forget to fill the following empty values:')
            ->expectsOutput('- Issue Tracker link')
            ->expectsOutput('- Figma link')
            ->expectsOutput('- Sentry link')
            ->expectsOutput('- DataDog link')
            ->expectsOutput('- ArgoCD link')
            ->expectsOutput('- Manager\'s email')
            ->expectsOutput('- Code Owner/Team Lead\'s email')
            ->expectsConfirmation('Do you want to install media package?')
            ->expectsConfirmation('Do you want to uninstall project-initializator package?')
            ->assertExitCode(0);
    }

    public function testRunWithAdminAndPartialReadmeCreation()
    {
        $this->mockFileGetContent(
            [
                'arguments' => ['.env.example'],
                'result' => $this->getFixture('env.example.yml'),
            ],
            [
                'arguments' => ['.env.development'],
                'result' => $this->getFixture('env.development.yml'),
            ],
            [
                'arguments' => ['/app/resources/md/readme/README.md'],
                'result' => $this->getTemplate('README.md'),
            ],
            [
                'arguments' => ['/app/resources/md/readme/RESOURCES_AND_CONTACTS.md'],
                'result' => $this->getTemplate('RESOURCES_AND_CONTACTS.md'),
            ],
            [
                'arguments' => ['/app/resources/md/readme/RESOURCES.md'],
                'result' => $this->getTemplate('RESOURCES.md'),
            ],
            [
                'arguments' => ['/app/resources/md/readme/CONTACTS.md'],
                'result' => $this->getTemplate('CONTACTS.md'),
            ],
            [
                'arguments' => ['/app/resources/md/readme/ENVIRONMENTS.md'],
                'result' => $this->getTemplate('ENVIRONMENTS.md'),
            ],
            [
                'arguments' => ['/app/resources/md/readme/CREDENTIALS_AND_ACCESS.md'],
                'result' => $this->getTemplate('CREDENTIALS_AND_ACCESS.md'),
            ],
        );

        $this->mockFilePutContent(
            [
                'README.md',
                $this->getFixture('partial_readme.md'),
                'optionalParameter',
                'optionalParameter',
            ]
        );

        $this->mockShellExec(
            ['arguments' => 'composer require ronasit/laravel-helpers --ansi'],
            ['arguments' => 'composer require ronasit/laravel-swagger --ansi'],
            ['arguments' => 'composer require --dev ronasit/laravel-entity-generator --ansi'],
            ['arguments' => 'composer require laravel/telescope --ansi'],
        );

        $this
            ->artisan('init "My App"')
            ->expectsOutput('Project initialized successfully!')
            ->expectsQuestion('Please enter an application URL', 'https://mysite.com')
            ->expectsConfirmation('Do you want to generate an admin user?')
            ->expectsConfirmation('Do you want to generate a README file?', 'yes')
            ->expectsQuestion('What type of application will your API serve?', 'Web')
            ->expectsConfirmation('Do you need a `Resources & Contacts` part?', 'yes')
            ->expectsQuestion(
                'Are you going to use Issue Tracker? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'later'
            )
            ->expectsQuestion(
                'Are you going to use Figma? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'no'
            )
            ->expectsQuestion(
                'Are you going to use Sentry? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'no'
            )
            ->expectsQuestion(
                'Are you going to use DataDog? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'no'
            )
            ->expectsQuestion(
                'Are you going to use ArgoCD? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'no'
            )
            ->expectsQuestion(
                'Are you going to use Laravel Telescope? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'no'
            )
            ->expectsQuestion(
                'Are you going to use Laravel Nova? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'no'
            )
            ->expectsQuestion('Please enter a Manager\'s email', 'manager@mail.com')
            ->expectsQuestion('Please enter a Code Owner/Team Lead\'s email', '')
            ->expectsConfirmation('Do you need a `Prerequisites` part?')
            ->expectsConfirmation('Do you need a `Getting Started` part?')
            ->expectsConfirmation('Do you need an `Environments` part?', 'yes')
            ->expectsConfirmation('Do you need a `Credentials and Access` part?', 'yes')
            ->expectsOutput('README generated successfully!')
            ->expectsOutput('Don`t forget to fill the following empty values:')
            ->expectsOutput('- Issue Tracker link')
            ->expectsOutput('- Code Owner/Team Lead\'s email')
            ->expectsConfirmation('Do you want to install media package?')
            ->expectsConfirmation('Do you want to uninstall project-initializator package?')
            ->assertExitCode(0);
    }

    public function testRunWithAdminAndFullReadmeCreationAndRemovingInitializatorInstallationMedia()
    {
        $this->mockFileGetContent(
            [
                'arguments' => ['.env.example'],
                'result' => $this->getFixture('env.example.yml'),
            ],
            [
                'arguments' => ['.env.development'],
                'result' => $this->getFixture('env.development.yml'),
            ],
            [
                'arguments' => ['/app/resources/md/readme/README.md'],
                'result' => $this->getTemplate('README.md'),
            ],
            [
                'arguments' => ['/app/resources/md/readme/RESOURCES_AND_CONTACTS.md'],
                'result' => $this->getTemplate('RESOURCES_AND_CONTACTS.md'),
            ],
            [
                'arguments' => ['/app/resources/md/readme/RESOURCES.md'],
                'result' => $this->getTemplate('RESOURCES.md'),
            ],
            [
                'arguments' => ['/app/resources/md/readme/CONTACTS.md'],
                'result' => $this->getTemplate('CONTACTS.md'),
            ],
            [
                'arguments' => ['/app/resources/md/readme/PREREQUISITES.md'],
                'result' => $this->getTemplate('PREREQUISITES.md'),
            ],
            [
                'arguments' => ['/app/resources/md/readme/GETTING_STARTED.md'],
                'result' => $this->getTemplate('GETTING_STARTED.md'),
            ],
            [
                'arguments' => ['/app/resources/md/readme/ENVIRONMENTS.md'],
                'result' => $this->getTemplate('ENVIRONMENTS.md'),
            ],
            [
                'arguments' => ['/app/resources/md/readme/CREDENTIALS_AND_ACCESS.md'],
                'result' => $this->getTemplate('CREDENTIALS_AND_ACCESS.md'),
            ],
        );

        $this->mockFilePutContent(
            [
                'database/migrations/2018_11_11_111111_add_default_user.php',
                $this->getFixture('migration.php'),
                'optionalParameter',
                'optionalParameter',
            ],
            [
                'README.md',
                $this->getFixture('full_readme.md'),
                'optionalParameter',
                'optionalParameter',
            ]
        );

        $this->mockShellExec(
            ['arguments' => 'git ls-remote --get-url origin', 'result' => 'https://github.com/ronasit/laravel-helpers.git'],
            ['arguments' => 'composer require ronasit/laravel-helpers --ansi'],
            ['arguments' => 'composer require ronasit/laravel-swagger --ansi'],
            ['arguments' => 'composer require --dev ronasit/laravel-entity-generator --ansi'],
            ['arguments' => 'composer require laravel/telescope --ansi'],
            ['arguments' => 'composer require ronasit/laravel-media --ansi'],
            ['arguments' => 'composer remove --dev ronasit/laravel-project-initializator --ansi'],
        );

        $this
            ->artisan('init "My App"')
            ->expectsOutput('Project initialized successfully!')
            ->expectsQuestion('Please enter an application URL', 'https://mysite.com')
            ->expectsConfirmation('Do you want to generate an admin user?', 'yes')
            ->expectsQuestion('Please enter an admin name', 'TestAdmin')
            ->expectsQuestion('Please enter an admin email', 'mail@mail.com')
            ->expectsQuestion('Please enter an admin password', '123456')
            ->expectsQuestion('Please enter an admin role id', 1)
            ->expectsConfirmation('Do you want to generate a README file?', 'yes')
            ->expectsQuestion('What type of application will your API serve?', 'Mobile')
            ->expectsConfirmation('Do you need a `Resources & Contacts` part?', 'yes')
            ->expectsQuestion(
                'Are you going to use Issue Tracker? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'https://gitlab.com/my-project'
            )
            ->expectsQuestion(
                'Are you going to use Figma? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'https://figma.com/my-project'
            )
            ->expectsQuestion(
                'Are you going to use Sentry? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'https://sentry.com/my-project'
            )
            ->expectsQuestion(
                'Are you going to use DataDog? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'https://datadoghq.com/my-project'
            )
            ->expectsQuestion(
                'Are you going to use ArgoCD? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'https://argocd.com/my-project'
            )
            ->expectsQuestion(
                'Are you going to use Laravel Telescope? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'https://mypsite.com/telescope-link'
            )
            ->expectsQuestion(
                'Are you going to use Laravel Nova? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'https://mypsite.com/nova-link'
            )
            ->expectsQuestion('Please enter a Manager\'s email', 'manager@mail.com')
            ->expectsQuestion('Please enter a Code Owner/Team Lead\'s email', 'lead@mail.com')
            ->expectsConfirmation('Do you need a `Prerequisites` part?', 'yes')
            ->expectsConfirmation('Do you need a `Getting Started` part?', 'yes')
            ->expectsConfirmation('Do you need an `Environments` part?', 'yes')
            ->expectsConfirmation('Do you need a `Credentials and Access` part?', 'yes')
            ->expectsConfirmation('Is Laravel Telescope\'s admin the same as default one?', 'yes')
            ->expectsConfirmation('Is Laravel Nova\'s admin the same as default one?')
            ->expectsQuestion('Please enter a Laravel Nova\'s admin email', 'nova_mail@mail.com')
            ->expectsQuestion('Please enter a Laravel Nova\'s admin password', '654321')
            ->expectsOutput('README generated successfully!')
            ->expectsConfirmation('Do you want to install media package?', 'yes')
            ->expectsConfirmation('Do you want to uninstall project-initializator package?', 'yes')
            ->assertExitCode(0);
    }
}
