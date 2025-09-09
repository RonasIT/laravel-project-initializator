<?php

namespace RonasIT\ProjectInitializator\Tests;

use RonasIT\ProjectInitializator\Tests\Support\Traits\InitCommandMockTrait;

class InitCommandTest extends TestCase
{
    use InitCommandMockTrait;

    public function testRunWithoutAdminAndReadmeCreationConvertAppNameToPascalCaseTelescopeAlreadyInstalled()
    {
        $this->mockLaravelConfigWriter(
            [
                'function' => 'is_file',
                'path' => '.env.example',
                'source' => $this->getFixture('env.example.yml'),
                'result' => $this->getFixture('env.example_app_name_pascal_case.yml'),
            ],
            [
                'function' => 'is_file',
                'path' => '.env.development',
                'source' => $this->getFixture('env.development.yml'),
                'result' => $this->getFixture('env.development_app_name_pascal_case.yml'),
            ],
            [
                'function' => 'file_exists',
                'path' => base_path('config/auto-doc.php'),
                'source' => $this->getFixture('auto_doc.php'),
                'result' => $this->getFixture('auto_doc_after_changes.php'),
            ],
        );

        $this->mockClassExists([
            'arguments' => ['Laravel\Telescope\TelescopeServiceProvider', true],
        ]);

        $this->mockShellExec(
            ['arguments' => 'composer require ronasit/laravel-helpers --ansi'],
            ['arguments' => 'composer require ronasit/laravel-swagger --ansi'],
            ['arguments' => 'php artisan vendor:publish --provider="RonasIT\AutoDoc\AutoDocServiceProvider" --ansi'],
            ['arguments' => 'composer require --dev ronasit/laravel-entity-generator --ansi'],
        );

        $this
            ->artisan('init "My App"')
            ->expectsConfirmation('The application name is not in PascalCase, would you like to use MyApp', 'yes')
            ->expectsQuestion("Please specify a Code Owner/Team Lead's email", 'test@example.com')
            ->expectsOutput('Project initialized successfully!')
            ->expectsQuestion('Please enter an application URL', 'https://mysite.com')
            ->expectsQuestion('What type of application will your API serve?', 'Multiplatform')
            ->expectsChoice('Please choose the authentication type', 'none', ['clerk', 'none'])
            ->expectsConfirmation('Do you want to generate an admin user?')
            ->expectsConfirmation('Do you want to generate a README file?')
            ->expectsConfirmation('Would you use Renovate dependabot?')
            ->expectsConfirmation('Do you want to install media package?')
            ->expectsConfirmation('Do you want to uninstall project-initializator package?')
            ->assertExitCode(0);
    }

    public function testRunWithoutAdminAndReadmeCreation()
    {
        $this->mockLaravelConfigWriter(
            [
                'function' => 'is_file',
                'path' => '.env.example',
                'source' => $this->getFixture('env.example.yml'),
                'result' => $this->getFixture('env.example_app_name_pascal_case.yml'),
            ],
            [
                'function' => 'is_file',
                'path' => '.env.development',
                'source' => $this->getFixture('env.development.yml'),
                'result' => $this->getFixture('env.development_app_name_pascal_case.yml'),
            ],
            [
                'function' => 'file_exists',
                'path' => base_path('config/auto-doc.php'),
                'source' => $this->getFixture('auto_doc.php'),
                'result' => $this->getFixture('auto_doc_after_changes.php'),
            ],
        );

        $this->mockFilePutContent( 
            [
                'renovate.json',
                $this->getFixture('renovate.json'),
            ],
        );

        $this->mockShellExec(
            ['arguments' => 'composer require ronasit/laravel-helpers --ansi'],
            ['arguments' => 'composer require ronasit/laravel-swagger --ansi'],
            ['arguments' => 'php artisan vendor:publish --provider="RonasIT\AutoDoc\AutoDocServiceProvider" --ansi'],
            ['arguments' => 'composer require --dev ronasit/laravel-entity-generator --ansi'],
            ['arguments' => 'composer require ronasit/laravel-telescope-extension --ansi'],
            ['arguments' => 'php artisan telescope:install --ansi'],
        );

        $this
            ->artisan('init "MyApp"')
            ->expectsQuestion("Please specify a Code Owner/Team Lead's email", 'test@example.com')
            ->expectsOutput('Project initialized successfully!')
            ->expectsQuestion('Please enter an application URL', 'https://mysite.com')
            ->expectsQuestion('What type of application will your API serve?', 'Multiplatform')
            ->expectsChoice('Please choose the authentication type', 'none', ['clerk', 'none'])
            ->expectsConfirmation('Do you want to generate an admin user?')
            ->expectsConfirmation('Do you want to generate a README file?')
            ->expectsConfirmation('Would you use Renovate dependabot?', 'yes')
            ->expectsQuestion('Please type username of the project reviewer', 'reviewer')
            ->expectsConfirmation('Do you want to install media package?')
            ->expectsConfirmation('Do you want to uninstall project-initializator package?')
            ->assertExitCode(0);
    }

    public function testRunWithAdminAndWithoutReadmeCreation()
    {
        $this->mockLaravelConfigWriter(
            [
                'function' => 'is_file',
                'path' => '.env.example',
                'source' => $this->getFixture('env.example.yml'),
                'result' => $this->getFixture('env.example_app_name_not_pascal_case.yml'),
            ],
            [
                'function' => 'is_file',
                'path' => '.env.development',
                'source' => $this->getFixture('env.development.yml'),
                'result' => $this->getFixture('env.development_app_name_not_pascal_case.yml'),
            ],
            [
                'function' => 'file_exists',
                'path' => base_path('config/auto-doc.php'),
                'source' => $this->getFixture('auto_doc.php'),
                'result' => $this->getFixture('auto_doc_after_changes.php'),
            ],
        );

        $this->mockFilePutContent(
            [
                'database/migrations/2018_11_11_111111_add_default_user.php',
                $this->getFixture('migration.php'),
            ],
        );

        $this->mockShellExec(
            ['arguments' => 'composer require ronasit/laravel-helpers --ansi'],
            ['arguments' => 'composer require ronasit/laravel-swagger --ansi'],
            ['arguments' => 'php artisan vendor:publish --provider="RonasIT\AutoDoc\AutoDocServiceProvider" --ansi'],
            ['arguments' => 'composer require --dev ronasit/laravel-entity-generator --ansi'],
            ['arguments' => 'composer require ronasit/laravel-telescope-extension --ansi'],
            ['arguments' => 'php artisan telescope:install --ansi'],
        );

        $this
            ->artisan('init "My App"')
            ->expectsConfirmation('The application name is not in PascalCase, would you like to use MyApp')
            ->expectsQuestion("Please specify a Code Owner/Team Lead's email", 'test@example.com')
            ->expectsOutput('Project initialized successfully!')
            ->expectsQuestion('Please enter an application URL', 'https://mysite.com')
            ->expectsQuestion('What type of application will your API serve?', 'Multiplatform')
            ->expectsChoice('Please choose the authentication type', 'none', ['clerk', 'none'])
            ->expectsConfirmation('Do you want to generate an admin user?', 'yes')
            ->expectsQuestion('Please enter an admin email', 'mail@mail.com')
            ->expectsQuestion('Please enter an admin password', '123456')
            ->expectsQuestion('Please enter an admin name', 'TestAdmin')
            ->expectsQuestion('Please enter an admin role id', 1)
            ->expectsConfirmation('Do you want to generate a README file?')
            ->expectsConfirmation('Would you use Renovate dependabot?')
            ->expectsConfirmation('Do you want to install media package?')
            ->expectsConfirmation('Do you want to uninstall project-initializator package?')
            ->assertExitCode(0);
    }

    public function testRunWithAdminAndDefaultReadmeCreation()
    {
        $this->mockLaravelConfigWriter(
            [
                'function' => 'is_file',
                'path' => '.env.example',
                'source' => $this->getFixture('env.example.yml'),
                'result' => $this->getFixture('env.example_app_name_not_pascal_case.yml'),
            ],
            [
                'function' => 'is_file',
                'path' => '.env.development',
                'source' => $this->getFixture('env.development.yml'),
                'result' => $this->getFixture('env.development_app_name_not_pascal_case.yml'),
            ],
            [
                'function' => 'is_file',
                'path' => '.env.development',
                'source' => $this->getFixture('env.development.yml'),
                'result' => $this->getFixture('env.development_clerk_credentials_added.yml'),
            ],
            [
                'function' => 'is_file',
                'path' => '.env.example',
                'source' => $this->getFixture('env.example.yml'),
                'result' => $this->getFixture('env.example_clerk_credentials_added.yml'),
            ],
            [
                'function' => 'file_exists',
                'path' => base_path('config/auto-doc.php'),
                'source' => $this->getFixture('auto_doc.php'),
                'result' => $this->getFixture('auto_doc_after_changes.php'),
            ],
        );

        $this->mockFileGetContent(
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/README.md')],
                'result' => $this->getTemplate('README.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/RESOURCES_AND_CONTACTS.md')],
                'result' => $this->getTemplate('RESOURCES_AND_CONTACTS.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/RESOURCES.md')],
                'result' => $this->getTemplate('RESOURCES.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/CONTACTS.md')],
                'result' => $this->getTemplate('CONTACTS.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/PREREQUISITES.md')],
                'result' => $this->getTemplate('PREREQUISITES.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/GETTING_STARTED.md')],
                'result' => $this->getTemplate('GETTING_STARTED.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/ENVIRONMENTS.md')],
                'result' => $this->getTemplate('ENVIRONMENTS.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/CREDENTIALS_AND_ACCESS.md')],
                'result' => $this->getTemplate('CREDENTIALS_AND_ACCESS.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/CLERK.md')],
                'result' => $this->getTemplate('CLERK.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/RENOVATE.md')],
                'result' => $this->getTemplate('RENOVATE.md'),
            ],
        );

        $this->mockFilePutContent(
            [
                'database/migrations/2018_11_11_111111_users_add_clerk_id_field.php',
                $this->getFixture('users_add_clerk_id_field_migration.php'),
            ],
            [
                'app/Support/Clerk/ClerkUserRepository.php',
                $this->getFixture('clerk_user_repository.php'),
            ],
            [
                'database/migrations/2018_11_11_111111_admins_create_table.php',
                $this->getFixture('admins_table_migration.php'),
            ],
            [
                'README.md',
                $this->getFixture('default_readme.md'),
            ],
            [
                'renovate.json',
                $this->getFixture('renovate.json'),
            ],
            [
                'README.md',
                $this->getFixture('default_readme_after_using_renovate.md'),
            ],
        );

        $this->mockShellExec(
            ['arguments' => 'git ls-remote --get-url origin', 'result' => 'https://github.com/ronasit/laravel-helpers.git'],
            ['arguments' => 'composer require ronasit/laravel-helpers --ansi'],
            ['arguments' => 'composer require ronasit/laravel-swagger --ansi'],
            ['arguments' => 'php artisan vendor:publish --provider="RonasIT\AutoDoc\AutoDocServiceProvider" --ansi'],
            ['arguments' => 'composer require --dev ronasit/laravel-entity-generator --ansi'],
            ['arguments' => 'composer require ronasit/laravel-clerk --ansi'],
            ['arguments' => 'php artisan laravel-clerk:install --ansi'],
            ['arguments' => 'composer require ronasit/laravel-telescope-extension --ansi'],
            ['arguments' => 'php artisan telescope:install --ansi'],
        );

        $this
            ->artisan('init "My App"')
            ->expectsConfirmation('The application name is not in PascalCase, would you like to use MyApp')
            ->expectsQuestion("Please specify a Code Owner/Team Lead's email", 'test@example.com')
            ->expectsOutput('Project initialized successfully!')
            ->expectsQuestion('Please enter an application URL', 'https://mysite.com')
            ->expectsQuestion('What type of application will your API serve?', 'Multiplatform')
            ->expectsChoice('Please choose the authentication type', 'clerk', ['clerk', 'none'])
            ->expectsConfirmation('Do you want to generate an admin user?', 'yes')
            ->expectsQuestion('Please enter an admin email', 'mail@mail.com')
            ->expectsQuestion('Please enter an admin password', '123456')
            ->expectsConfirmation('Do you want to generate a README file?', 'yes')
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
            ->expectsConfirmation('Would you use Renovate dependabot?', 'yes')
            ->expectsQuestion('Please type username of the project reviewer', 'reviewer')
            ->expectsConfirmation('Do you want to install media package?')
            ->expectsConfirmation('Do you want to uninstall project-initializator package?')
            ->assertExitCode(0);
    }

    public function testRunWithAdminAndPartialReadmeCreation()
    {
        $this->mockLaravelConfigWriter(
            [
                'function' => 'is_file',
                'path' => '.env.example',
                'source' => $this->getFixture('env.example.yml'),
                'result' => $this->getFixture('env.example_app_name_not_pascal_case.yml'),
            ],
            [
                'function' => 'is_file',
                'path' => '.env.development',
                'source' => $this->getFixture('env.development.yml'),
                'result' => $this->getFixture('env.development_app_name_not_pascal_case.yml'),
            ],
            [
                'function' => 'file_exists',
                'path' => base_path('config/auto-doc.php'),
                'source' => $this->getFixture('auto_doc.php'),
                'result' => $this->getFixture('auto_doc_after_changes.php'),
            ],
        );

        $this->mockFileGetContent(
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/README.md')],
                'result' => $this->getTemplate('README.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/RESOURCES_AND_CONTACTS.md')],
                'result' => $this->getTemplate('RESOURCES_AND_CONTACTS.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/RESOURCES.md')],
                'result' => $this->getTemplate('RESOURCES.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/CONTACTS.md')],
                'result' => $this->getTemplate('CONTACTS.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/ENVIRONMENTS.md')],
                'result' => $this->getTemplate('ENVIRONMENTS.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/CREDENTIALS_AND_ACCESS.md')],
                'result' => $this->getTemplate('CREDENTIALS_AND_ACCESS.md'),
            ],
        );

        $this->mockFilePutContent(
            [
                'README.md',
                $this->getFixture('partial_readme.md'),
            ],
        );

        $this->mockShellExec(
            ['arguments' => 'composer require ronasit/laravel-helpers --ansi'],
            ['arguments' => 'composer require ronasit/laravel-swagger --ansi'],
            ['arguments' => 'php artisan vendor:publish --provider="RonasIT\AutoDoc\AutoDocServiceProvider" --ansi'],
            ['arguments' => 'composer require --dev ronasit/laravel-entity-generator --ansi'],
            ['arguments' => 'composer require ronasit/laravel-telescope-extension --ansi'],
            ['arguments' => 'php artisan telescope:install --ansi'],
        );

        $this
            ->artisan('init "My App"')
            ->expectsConfirmation('The application name is not in PascalCase, would you like to use MyApp')
            ->expectsQuestion("Please specify a Code Owner/Team Lead's email", 'test@example.com')
            ->expectsOutput('Project initialized successfully!')
            ->expectsQuestion('Please enter an application URL', 'https://mysite.com')
            ->expectsQuestion('What type of application will your API serve?', 'Web')
            ->expectsChoice('Please choose the authentication type', 'none', ['clerk', 'none'])
            ->expectsConfirmation('Do you want to generate an admin user?')
            ->expectsConfirmation('Do you want to generate a README file?', 'yes')
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
            ->expectsConfirmation('Do you need a `Prerequisites` part?')
            ->expectsConfirmation('Do you need a `Getting Started` part?')
            ->expectsConfirmation('Do you need an `Environments` part?', 'yes')
            ->expectsConfirmation('Do you need a `Credentials and Access` part?', 'yes')
            ->expectsOutput('README generated successfully!')
            ->expectsOutput('Don`t forget to fill the following empty values:')
            ->expectsOutput('- Issue Tracker link')
            ->expectsConfirmation('Would you use Renovate dependabot?')
            ->expectsConfirmation('Do you want to install media package?')
            ->expectsConfirmation('Do you want to uninstall project-initializator package?')
            ->assertExitCode(0);
    }

    public function testRunWithAdminAndFullReadmeCreationAndRemovingInitializatorInstallationMedia()
    {
        $this->mockLaravelConfigWriter(
            [
                'function' => 'is_file',
                'path' => '.env.example',
                'source' => $this->getFixture('env.example.yml'),
                'result' => $this->getFixture('env.example_app_name_not_pascal_case.yml'),
            ],
            [
                'function' => 'is_file',
                'path' => '.env.development',
                'source' => $this->getFixture('env.development.yml'),
                'result' => $this->getFixture('env.development_app_name_not_pascal_case.yml'),
            ],
            [
                'function' => 'file_exists',
                'path' => base_path('config/auto-doc.php'),
                'source' => $this->getFixture('auto_doc.php'),
                'result' => $this->getFixture('auto_doc_after_changes.php'),
            ],
        );

        $this->mockFileGetContent(
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/README.md')],
                'result' => $this->getTemplate('README.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/RESOURCES_AND_CONTACTS.md')],
                'result' => $this->getTemplate('RESOURCES_AND_CONTACTS.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/RESOURCES.md')],
                'result' => $this->getTemplate('RESOURCES.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/CONTACTS.md')],
                'result' => $this->getTemplate('CONTACTS.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/PREREQUISITES.md')],
                'result' => $this->getTemplate('PREREQUISITES.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/GETTING_STARTED.md')],
                'result' => $this->getTemplate('GETTING_STARTED.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/ENVIRONMENTS.md')],
                'result' => $this->getTemplate('ENVIRONMENTS.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/CREDENTIALS_AND_ACCESS.md')],
                'result' => $this->getTemplate('CREDENTIALS_AND_ACCESS.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/RENOVATE.md')],
                'result' => $this->getTemplate('RENOVATE.md'),
            ],
        );

        $this->mockFilePutContent(
            [
                'database/migrations/2018_11_11_111111_add_default_user.php',
                $this->getFixture('migration.php'),
            ],
            [
                'README.md',
                $this->getFixture('full_readme.md'),
            ],
            [
                'renovate.json',
                $this->getFixture('renovate.json'),
            ],
            [
                'README.md',
                $this->getFixture('full_readme_after_using_renovate.md'),
            ],
        );

        $this->mockShellExec(
            ['arguments' => 'git ls-remote --get-url origin', 'result' => 'https://github.com/ronasit/laravel-helpers.git'],
            ['arguments' => 'composer require ronasit/laravel-helpers --ansi'],
            ['arguments' => 'composer require ronasit/laravel-swagger --ansi'],
            ['arguments' => 'php artisan vendor:publish --provider="RonasIT\AutoDoc\AutoDocServiceProvider" --ansi'],
            ['arguments' => 'composer require --dev ronasit/laravel-entity-generator --ansi'],
            ['arguments' => 'composer require ronasit/laravel-telescope-extension --ansi'],
            ['arguments' => 'php artisan telescope:install --ansi'],
            ['arguments' => 'composer require ronasit/laravel-media --ansi'],
            ['arguments' => 'composer remove --dev ronasit/laravel-project-initializator --ansi'],
        );

        $this
            ->artisan('init "My App"')
            ->expectsConfirmation('The application name is not in PascalCase, would you like to use MyApp')
            ->expectsQuestion("Please specify a Code Owner/Team Lead's email", 'test@example.com')
            ->expectsOutput('Project initialized successfully!')
            ->expectsQuestion('Please enter an application URL', 'https://mysite.com')
            ->expectsQuestion('What type of application will your API serve?', 'Mobile')
            ->expectsChoice('Please choose the authentication type', 'none', ['clerk', 'none'])
            ->expectsConfirmation('Do you want to generate an admin user?', 'yes')
            ->expectsQuestion('Please enter an admin email', 'mail@mail.com')
            ->expectsQuestion('Please enter an admin password', '123456')
            ->expectsQuestion('Please enter an admin name', 'TestAdmin')
            ->expectsQuestion('Please enter an admin role id', 1)
            ->expectsConfirmation('Do you want to generate a README file?', 'yes')
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
            ->expectsConfirmation('Do you need a `Prerequisites` part?', 'yes')
            ->expectsConfirmation('Do you need a `Getting Started` part?', 'yes')
            ->expectsConfirmation('Do you need an `Environments` part?', 'yes')
            ->expectsConfirmation('Do you need a `Credentials and Access` part?', 'yes')
            ->expectsConfirmation('Is Laravel Telescope\'s admin the same as default one?', 'yes')
            ->expectsConfirmation('Is Laravel Nova\'s admin the same as default one?')
            ->expectsQuestion('Please enter a Laravel Nova\'s admin email', 'nova_mail@mail.com')
            ->expectsQuestion('Please enter a Laravel Nova\'s admin password', '654321')
            ->expectsOutput('README generated successfully!')
            ->expectsConfirmation('Would you use Renovate dependabot?', 'yes')
            ->expectsQuestion('Please type username of the project reviewer', 'reviewer')
            ->expectsConfirmation('Do you want to install media package?', 'yes')
            ->expectsConfirmation('Do you want to uninstall project-initializator package?', 'yes')
            ->assertExitCode(0);
    }

    public function testRunWithoutAdminAndUsingTelescope()
    {
        $this->mockLaravelConfigWriter(
            [
                'function' => 'is_file',
                'path' => '.env.example',
                'source' => $this->getFixture('env.example.yml'),
                'result' => $this->getFixture('env.example_app_name_not_pascal_case.yml'),
            ],
            [
                'function' => 'is_file',
                'path' => '.env.development',
                'source' => $this->getFixture('env.development.yml'),
                'result' => $this->getFixture('env.development_app_name_not_pascal_case.yml'),
            ],
            [
                'function' => 'file_exists',
                'path' => base_path('config/auto-doc.php'),
                'source' => $this->getFixture('auto_doc.php'),
                'result' => $this->getFixture('auto_doc_after_changes.php'),
            ],
        );

        $this->mockFileGetContent(
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/README.md')],
                'result' => $this->getTemplate('README.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/RESOURCES_AND_CONTACTS.md')],
                'result' => $this->getTemplate('RESOURCES_AND_CONTACTS.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/RESOURCES.md')],
                'result' => $this->getTemplate('RESOURCES.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/CONTACTS.md')],
                'result' => $this->getTemplate('CONTACTS.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/ENVIRONMENTS.md')],
                'result' => $this->getTemplate('ENVIRONMENTS.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/CREDENTIALS_AND_ACCESS.md')],
                'result' => $this->getTemplate('CREDENTIALS_AND_ACCESS.md'),
            ],
        );

        $this->mockFilePutContent(
            [
                'README.md',
                $this->getFixture('partial_readme_with_telescope.md'),
            ],
        );

        $this->mockShellExec(
            ['arguments' => 'composer require ronasit/laravel-helpers --ansi'],
            ['arguments' => 'composer require ronasit/laravel-swagger --ansi'],
            ['arguments' => 'php artisan vendor:publish --provider="RonasIT\AutoDoc\AutoDocServiceProvider" --ansi'],
            ['arguments' => 'composer require --dev ronasit/laravel-entity-generator --ansi'],
            ['arguments' => 'composer require ronasit/laravel-telescope-extension --ansi'],
            ['arguments' => 'php artisan telescope:install --ansi'],
        );

        $this
            ->artisan('init "My App"')
            ->expectsConfirmation('The application name is not in PascalCase, would you like to use MyApp')
            ->expectsQuestion("Please specify a Code Owner/Team Lead's email", 'test@example.com')
            ->expectsOutput('Project initialized successfully!')
            ->expectsQuestion('Please enter an application URL', 'https://mysite.com')
            ->expectsQuestion('What type of application will your API serve?', 'Web')
            ->expectsChoice('Please choose the authentication type', 'none', ['clerk', 'none'])
            ->expectsConfirmation('Do you want to generate an admin user?')
            ->expectsConfirmation('Do you want to generate a README file?', 'yes')
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
                'later'
            )
            ->expectsQuestion(
                'Are you going to use Laravel Nova? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'later'
            )
            ->expectsQuestion('Please enter a Manager\'s email', 'manager@mail.com')
            ->expectsConfirmation('Do you need a `Prerequisites` part?')
            ->expectsConfirmation('Do you need a `Getting Started` part?')
            ->expectsConfirmation('Do you need an `Environments` part?', 'yes')
            ->expectsConfirmation('Do you need a `Credentials and Access` part?', 'yes')
            ->expectsQuestion('Please enter a Laravel Telescope\'s admin email', 'telescope_mail@mail.com')
            ->expectsQuestion('Please enter a Laravel Telescope\'s admin password', '654321')
            ->expectsQuestion('Please enter a Laravel Nova\'s admin email', 'nova_mail@mail.com')
            ->expectsQuestion('Please enter a Laravel Nova\'s admin password', '654321')
            ->expectsOutput('README generated successfully!')
            ->expectsOutput('Don`t forget to fill the following empty values:')
            ->expectsOutput('- Issue Tracker link')
            ->expectsConfirmation('Would you use Renovate dependabot?')
            ->expectsConfirmation('Do you want to install media package?')
            ->expectsConfirmation('Do you want to uninstall project-initializator package?')
            ->assertExitCode(0);
    }

    public function testRunWithClerkMobileApp(): void
    {
        $this->mockLaravelConfigWriter(
            [
                'function' => 'is_file',
                'path' => '.env.example',
                'source' => $this->getFixture('env.example.yml'),
                'result' => $this->getFixture('env.example_app_name_not_pascal_case.yml'),
            ],
            [
                'function' => 'is_file',
                'path' => '.env.development',
                'source' => $this->getFixture('env.development.yml'),
                'result' => $this->getFixture('env.development_app_name_not_pascal_case.yml'),
            ],
            [
                'function' => 'is_file',
                'path' => '.env.development',
                'source' => $this->getFixture('env.development.yml'),
                'result' => $this->getFixture('env.development_clerk_credentials_added_mobile_app.yml'),
            ],
            [
                'function' => 'is_file',
                'path' => '.env.example',
                'source' => $this->getFixture('env.example.yml'),
                'result' => $this->getFixture('env.example_clerk_credentials_added_mobile_app.yml'),
            ],
            [
                'function' => 'file_exists',
                'path' => base_path('config/auto-doc.php'),
                'source' => $this->getFixture('auto_doc.php'),
                'result' => $this->getFixture('auto_doc_after_changes.php'),
            ],
        );

        $this->mockFileGetContent(
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/README.md')],
                'result' => $this->getTemplate('README.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/RESOURCES_AND_CONTACTS.md')],
                'result' => $this->getTemplate('RESOURCES_AND_CONTACTS.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/RESOURCES.md')],
                'result' => $this->getTemplate('RESOURCES.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/CONTACTS.md')],
                'result' => $this->getTemplate('CONTACTS.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/PREREQUISITES.md')],
                'result' => $this->getTemplate('PREREQUISITES.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/GETTING_STARTED.md')],
                'result' => $this->getTemplate('GETTING_STARTED.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/ENVIRONMENTS.md')],
                'result' => $this->getTemplate('ENVIRONMENTS.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/CREDENTIALS_AND_ACCESS.md')],
                'result' => $this->getTemplate('CREDENTIALS_AND_ACCESS.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/CLERK.md')],
                'result' => $this->getTemplate('CLERK.md'),
            ],
            [
                'arguments' => [base_path('/vendor/ronasit/laravel-project-initializator/resources/md/readme/RENOVATE.md')],
                'result' => $this->getTemplate('RENOVATE.md'),
            ],
        );

        $this->mockFilePutContent(
            [
                'database/migrations/2018_11_11_111111_users_add_clerk_id_field.php',
                $this->getFixture('users_add_clerk_id_field_migration.php'),
            ],
            [
                'app/Support/Clerk/ClerkUserRepository.php',
                $this->getFixture('clerk_user_repository.php'),
            ],
            [
                'database/migrations/2018_11_11_111111_admins_create_table.php',
                $this->getFixture('admins_table_migration.php'),
            ],
            [
                'README.md',
                $this->getFixture('default_readme_with_mobile_app.md'),
            ],
            [
                'renovate.json',
                $this->getFixture('renovate.json'),
            ],
            [
                'README.md',
                $this->getFixture('default_readme_with_mobile_app_after_using_renovate.md'),
            ],
        );

        $this->mockShellExec(
            ['arguments' => 'git ls-remote --get-url origin', 'result' => 'https://github.com/ronasit/laravel-helpers.git'],
            ['arguments' => 'composer require ronasit/laravel-helpers --ansi'],
            ['arguments' => 'composer require ronasit/laravel-swagger --ansi'],
            ['arguments' => 'php artisan vendor:publish --provider="RonasIT\AutoDoc\AutoDocServiceProvider" --ansi'],
            ['arguments' => 'composer require --dev ronasit/laravel-entity-generator --ansi'],
            ['arguments' => 'composer require ronasit/laravel-clerk --ansi'],
            ['arguments' => 'php artisan laravel-clerk:install --ansi'],
            ['arguments' => 'composer require ronasit/laravel-telescope-extension --ansi'],
            ['arguments' => 'php artisan telescope:install --ansi'],
        );

        $this
            ->artisan('init "My App"')
            ->expectsConfirmation('The application name is not in PascalCase, would you like to use MyApp')
            ->expectsQuestion("Please specify a Code Owner/Team Lead's email", 'test@example.com')
            ->expectsOutput('Project initialized successfully!')
            ->expectsQuestion('Please enter an application URL', 'https://mysite.com')
            ->expectsQuestion('What type of application will your API serve?', 'Mobile')
            ->expectsChoice('Please choose the authentication type', 'clerk', ['clerk', 'none'])
            ->expectsConfirmation('Do you want to generate an admin user?', 'yes')
            ->expectsQuestion('Please enter an admin email', 'mail@mail.com')
            ->expectsQuestion('Please enter an admin password', '123456')
            ->expectsConfirmation('Do you want to generate a README file?', 'yes')
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
            ->expectsConfirmation('Would you use Renovate dependabot?', 'yes')
            ->expectsQuestion('Please type username of the project reviewer', 'reviewer')
            ->expectsConfirmation('Do you want to install media package?')
            ->expectsConfirmation('Do you want to uninstall project-initializator package?')
            ->assertExitCode(0);
    }
}
