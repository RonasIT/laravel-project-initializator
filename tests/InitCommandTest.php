<?php

namespace RonasIT\ProjectInitializator\Tests;

use Mockery;
use ReflectionMethod;
use ReflectionProperty;
use RonasIT\ProjectInitializator\Commands\InitCommand;
use RonasIT\ProjectInitializator\Enums\AuthTypeEnum;
use RonasIT\ProjectInitializator\Tests\Support\Traits\InitCommandMockTrait;

class InitCommandTest extends TestCase
{
    use InitCommandMockTrait;

    public function testRunWithoutAdminAndReadmeCreationConvertAppNameToPascalCaseTelescopeAlreadyInstalled()
    {
        $this->mockNativeFunction(
            '\Winter\LaravelConfigWriter',
            $this->changeEnvFileCall('.env.example', 'env.example.yml', 'env.example_app_name_pascal_case.yml'),
            $this->changeEnvFileCall('.env.development', 'env.development.yml', 'env.development_app_name_pascal_case.yml'),
            $this->changeConfigFileCall('config/auto-doc.php', 'auto_doc.php', 'auto_doc_after_changes.php'),
            $this->changeConfigFileCall('config/telescope.php', 'telescope_config.php', 'telescope_config_after_initialization.php'),
        );

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('bootstrap/app.php', $this->getFixture('app.php')),
            $this->callFilePutContent('bootstrap/app.php', $this->getFixture('app_after_changes.php')),
        );

        $this->mockNativeFunction(
            'RonasIT\ProjectInitializator\Commands',
            $this->callFileExists('.env', false),
            $this->callFileExists('.env.development', false),

            $this->callCopy('.env.example', '.env'),
            $this->callCopy('.env.example', '.env.development'),

            $this->callClassExists('Laravel\Telescope\TelescopeServiceProvider'),

            $this->callFileGetContent(base_path('composer.json'), $this->getFixture('composer_with_pint_settings.json')),

            $this->callGlob(base_path('database/migrations/*_roles_create_table.php'), []),
            $this->callGlob(base_path('database/migrations/*_create_roles_table.php'), []),
            $this->callFilePutContent('database/migrations/2018_11_11_111112_roles_create_table.php', $this->getFixture('roles_create_table_migration.php')),
            $this->callFilePutContent('database/migrations/2018_11_11_111113_users_add_role_id.php', $this->getFixture('users_add_role_id_migration.php')),
            $this->callFilePutContent(base_path('composer.json'), $this->getFixture('composer_with_pint_settings.json')),
            $this->callFilePutContent(base_path('/routes/web.php'), "\nAuth::routes();\n", FILE_APPEND),

            $this->callShellExec('composer require laravel/ui --ansi'),
            $this->callShellExec('composer require ronasit/laravel-helpers --ansi'),
            $this->callShellExec('composer require ronasit/laravel-swagger --ansi'),
            $this->callShellExec('php artisan vendor:publish --provider="RonasIT\AutoDoc\AutoDocServiceProvider" --ansi'),
            $this->callShellExec('composer require --dev ronasit/laravel-entity-generator --ansi'),
            $this->callShellExec('composer require --dev laravel/pint --ansi'),
            $this->callShellExec('php artisan vendor:publish --tag=pint-config --ansi'),
            $this->callShellExec('composer require --dev brainmaestro/composer-git-hooks --ansi'),
            $this->callShellExec('./vendor/bin/cghooks update --ansi'),
            $this->callShellExec('php artisan lang:publish --ansi'),
            $this->callShellExec('php artisan vendor:publish --tag=initializator-web-login --force'),
            $this->callShellExec('php artisan migrate --ansi --force'),
        );

        $this
            ->artisan('init "My App"')
            ->expectsConfirmation('The application name is not in PascalCase, would you like to use MyApp', 'yes')
            ->expectsQuestion('Please specify: email of code owner / team lead', 'test@example.com')
            ->expectsOutput('Project initialized successfully!')
            ->expectsQuestion('Please enter an application URL', 'https://mysite.com')
            ->expectsQuestion('What type of application will your API serve?', 'Multiplatform')
            ->expectsChoice('Please choose the authentication type', 'none', ['clerk', 'none'])
            ->expectsConfirmation('Do you want to generate an admin user?')
            ->expectsConfirmation('Do you want to generate a README file?')
            ->expectsConfirmation('Will project work with media files? (upload, store and return content)')
            ->expectsConfirmation('Would you use Renovate dependabot?')
            ->expectsConfirmation('Do you want to uninstall project-initializator package?')
            ->assertExitCode(0);
    }

    public function testRunWithoutAdminAndReadmeCreation()
    {
        $this->mockNativeFunction(
            '\Winter\LaravelConfigWriter',
            $this->changeEnvFileCall('.env.example', 'env.example.yml', 'env.example_app_name_pascal_case.yml'),
            $this->changeEnvFileCall('.env', 'env.example.yml', 'env.example_app_name_pascal_case.yml'),
            $this->changeEnvFileCall('.env.development', 'env.development.yml', 'env.development_app_name_pascal_case.yml'),
            $this->changeConfigFileCall('config/auto-doc.php', 'auto_doc.php', 'auto_doc_after_changes.php'),
            $this->changeConfigFileCall('config/telescope.php', 'telescope_config.php', 'telescope_config_after_initialization.php'),
        );

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('bootstrap/app.php', $this->getFixture('app.php')),
            $this->callFilePutContent('bootstrap/app.php', $this->getFixture('app_after_changes.php')),
        );

        $this->mockNativeFunction(
            'RonasIT\ProjectInitializator\Commands',
            $this->callFileExists('.env'),
            $this->callFileExists('.env.development'),

            $this->callFileGetContent(base_path('composer.json'), $this->getFixture('composer_with_pint_settings.json')),

            $this->callGlob(base_path('database/migrations/*_roles_create_table.php'), []),
            $this->callGlob(base_path('database/migrations/*_create_roles_table.php'), []),
            $this->callFilePutContent('database/migrations/2018_11_11_111112_roles_create_table.php', $this->getFixture('roles_create_table_migration.php')),
            $this->callFilePutContent('database/migrations/2018_11_11_111113_users_add_role_id.php', $this->getFixture('users_add_role_id_migration.php')),
            $this->callFilePutContent('renovate.json', $this->getFixture('renovate.json')),
            $this->callFilePutContent(base_path('composer.json'), $this->getFixture('composer_with_pint_settings.json')),
            $this->callFilePutContent(base_path('/routes/web.php'), "\nAuth::routes();\n", FILE_APPEND),

            $this->callShellExec('composer require laravel/ui --ansi'),
            $this->callShellExec('composer require ronasit/laravel-helpers --ansi'),
            $this->callShellExec('composer require ronasit/laravel-swagger --ansi'),
            $this->callShellExec('php artisan vendor:publish --provider="RonasIT\AutoDoc\AutoDocServiceProvider" --ansi'),
            $this->callShellExec('composer require --dev ronasit/laravel-entity-generator --ansi'),
            $this->callShellExec('composer require --dev laravel/pint --ansi'),
            $this->callShellExec('php artisan vendor:publish --tag=pint-config --ansi'),
            $this->callShellExec('composer require --dev brainmaestro/composer-git-hooks --ansi'),
            $this->callShellExec('./vendor/bin/cghooks update --ansi'),
            $this->callShellExec('php artisan lang:publish --ansi'),
            $this->callShellExec('composer require ronasit/laravel-telescope-extension --ansi'),
            $this->callShellExec('php artisan telescope:install --ansi'),
            $this->callShellExec('php artisan vendor:publish --tag=initializator-web-login --force'),
            $this->callShellExec('php artisan migrate --ansi --force'),
        );

        $this
            ->artisan('init "MyApp"')
            ->expectsQuestion('Please specify: email of code owner / team lead', 'test@example.com')
            ->expectsOutput('Project initialized successfully!')
            ->expectsQuestion('Please enter an application URL', 'https://mysite.com')
            ->expectsQuestion('What type of application will your API serve?', 'Multiplatform')
            ->expectsChoice('Please choose the authentication type', 'none', ['clerk', 'none'])
            ->expectsConfirmation('Do you want to generate an admin user?')
            ->expectsConfirmation('Do you want to generate a README file?')
            ->expectsConfirmation('Will project work with media files? (upload, store and return content)')
            ->expectsConfirmation('Would you use Renovate dependabot?', 'yes')
            ->expectsQuestion('Please specify: username of the project reviewer', 'reviewer')
            ->expectsConfirmation('Do you want to uninstall project-initializator package?')
            ->assertExitCode(0);
    }

    public function testRunWithAdminAndWithoutReadmeCreation()
    {
        $this->mockNativeFunction(
            '\Winter\LaravelConfigWriter',
            $this->changeEnvFileCall('.env.example', 'env.example.yml', 'env.example_app_name_not_pascal_case.yml'),
            $this->changeEnvFileCall('.env.development', 'env.development.yml', 'env.development_app_name_not_pascal_case.yml'),
            $this->changeConfigFileCall('config/auto-doc.php', 'auto_doc.php', 'auto_doc_after_changes.php'),
            $this->changeConfigFileCall('config/telescope.php', 'telescope_config.php', 'telescope_config_after_initialization.php'),
        );

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('bootstrap/app.php', $this->getFixture('app.php')),
            $this->callFilePutContent('bootstrap/app.php', $this->getFixture('app_after_changes.php')),
        );

        $this->mockNativeFunction(
            'RonasIT\ProjectInitializator\Commands',
            $this->callFileExists('.env', false),
            $this->callFileExists('.env.development'),

            $this->callCopy('.env.example', '.env'),

            $this->callFileGetContent(base_path('composer.json'), $this->getFixture('composer_with_pint_settings.json')),

            $this->callGlob(base_path('database/migrations/*_roles_create_table.php'), []),
            $this->callGlob(base_path('database/migrations/*_create_roles_table.php'), []),
            $this->callFilePutContent('database/migrations/2018_11_11_111112_roles_create_table.php', $this->getFixture('roles_create_table_migration.php')),
            $this->callFilePutContent('database/migrations/2018_11_11_111113_users_add_role_id.php', $this->getFixture('users_add_role_id_migration.php')),
            $this->callFilePutContent('database/migrations/2018_11_11_111114_add_default_admin.php', $this->getFixture('migration.php')),
            $this->callFilePutContent(base_path('composer.json'), $this->getFixture('composer_with_pint_settings.json')),
            $this->callFilePutContent(base_path('/routes/web.php'), "\nAuth::routes();\n", FILE_APPEND),

            $this->callShellExec('composer require laravel/ui --ansi'),
            $this->callShellExec('composer require ronasit/laravel-helpers --ansi'),
            $this->callShellExec('composer require ronasit/laravel-swagger --ansi'),
            $this->callShellExec('php artisan vendor:publish --provider="RonasIT\AutoDoc\AutoDocServiceProvider" --ansi'),
            $this->callShellExec('composer require --dev ronasit/laravel-entity-generator --ansi'),
            $this->callShellExec('composer require --dev laravel/pint --ansi'),
            $this->callShellExec('php artisan vendor:publish --tag=pint-config --ansi'),
            $this->callShellExec('composer require --dev brainmaestro/composer-git-hooks --ansi'),
            $this->callShellExec('./vendor/bin/cghooks update --ansi'),
            $this->callShellExec('php artisan lang:publish --ansi'),
            $this->callShellExec('composer require ronasit/laravel-telescope-extension --ansi'),
            $this->callShellExec('php artisan telescope:install --ansi'),
            $this->callShellExec('php artisan vendor:publish --tag=initializator-web-login --force'),
            $this->callShellExec('php artisan migrate --ansi --force'),
        );

        $this
            ->artisan('init "My App"')
            ->expectsConfirmation('The application name is not in PascalCase, would you like to use MyApp')
            ->expectsQuestion('Please specify: email of code owner / team lead', 'test@example.com')
            ->expectsOutput('Project initialized successfully!')
            ->expectsQuestion('Please enter an application URL', 'https://mysite.com')
            ->expectsQuestion('What type of application will your API serve?', 'Multiplatform')
            ->expectsChoice('Please choose the authentication type', 'none', ['clerk', 'none'])
            ->expectsConfirmation('Do you want to generate an admin user?', 'yes')
            ->expectsQuestion('Please enter admin email', 'mail@mail.com')
            ->expectsQuestion('Please enter admin password', '123456')
            ->expectsQuestion('Please enter admin name', 'TestAdmin')
            ->expectsQuestion('Please enter admin role id', 1)
            ->expectsConfirmation('Do you want to generate a README file?')
            ->expectsConfirmation('Will project work with media files? (upload, store and return content)')
            ->expectsConfirmation('Would you use Renovate dependabot?')
            ->expectsConfirmation('Do you want to uninstall project-initializator package?')
            ->assertExitCode(0);
    }

    public function testRunWithAdminAndDefaultReadmeCreation()
    {
        $this->mockNativeFunction(
            '\Winter\LaravelConfigWriter',
            $this->changeEnvFileCall('.env.example', 'env.example.yml', 'env.example_app_name_not_pascal_case.yml'),
            $this->changeEnvFileCall('.env.development', 'env.development.yml', 'env.development_app_name_not_pascal_case.yml'),
            $this->changeEnvFileCall('.env', 'env.example_app_name_not_pascal_case.yml', 'env.example_clerk_credentials_added.yml'),
            $this->changeEnvFileCall('.env.example', 'env.example_app_name_not_pascal_case.yml', 'env.example_clerk_credentials_added.yml'),
            $this->changeEnvFileCall('.env.development', 'env.development_app_name_not_pascal_case.yml', 'env.development_clerk_credentials_added.yml'),
            $this->changeConfigFileCall('config/auto-doc.php', 'auto_doc.php', 'auto_doc_after_changes.php'),
            $this->changeConfigFileCall('config/telescope.php', 'telescope_config.php', 'telescope_config_after_initialization.php'),
        );

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent(app_path('Models/User.php'), $this->getFixture('user_model.php')),
            $this->callFilePutContent(app_path('Models/User.php'), $this->getFixture('user_model_fillable_updated.php')),
            $this->callFileGetContent('bootstrap/app.php', $this->getFixture('app.php')),
            $this->callFilePutContent('bootstrap/app.php', $this->getFixture('app_after_changes.php')),
        );

        $this->mockNativeFunction(
            'RonasIT\ProjectInitializator\Commands',
            $this->callFileExists('.env', false),
            $this->callFileExists('.env.development'),

            $this->callCopy('.env.example', '.env'),

            $this->callFileGetContent(base_path('composer.json'), $this->getFixture('composer_with_pint_settings.json')),

            $this->callFilePutContent('database/migrations/2018_11_11_111112_users_format_to_clerk.php', $this->getFixture('users_format_to_clerk_migration.php')),
            $this->callFilePutContent('app/Support/Clerk/ClerkUserRepository.php', $this->getFixture('clerk_user_repository.php')),
            $this->callFilePutContent('database/migrations/2018_11_11_111113_admins_create_table.php', $this->getFixture('admins_table_migration.php')),
            $this->callFilePutContent('database/migrations/2018_11_11_111114_add_default_admin.php', $this->getFixture('admins_add_default_admin.php')),
            $this->callGlob(base_path('database/migrations/*_admins_create_table.php'), [base_path('database/migrations/2018_11_11_111113_admins_create_table.php')]),
            $this->callFilePutContent('database/migrations/2018_11_11_111115_add_nova_admin.php', $this->getFixture('admins_add_nova_admin_migration.php')),
            $this->callFilePutContent('renovate.json', $this->getFixture('renovate.json')),
            $this->callFilePutContent(base_path('composer.json'), $this->getFixture('composer_with_pint_settings.json')),
            $this->callFilePutContent(base_path('/routes/web.php'), "\nAuth::routes();\n", FILE_APPEND),

            $this->callShellExec('git ls-remote --get-url origin', 'https://github.com/ronasit/laravel-helpers.git'),
            $this->callShellExec('composer require laravel/ui --ansi'),
            $this->callShellExec('composer require ronasit/laravel-helpers --ansi'),
            $this->callShellExec('composer require ronasit/laravel-swagger --ansi'),
            $this->callShellExec('php artisan vendor:publish --provider="RonasIT\AutoDoc\AutoDocServiceProvider" --ansi'),
            $this->callShellExec('composer require --dev ronasit/laravel-entity-generator --ansi'),
            $this->callShellExec('composer require --dev laravel/pint --ansi'),
            $this->callShellExec('php artisan vendor:publish --tag=pint-config --ansi'),
            $this->callShellExec('composer require --dev brainmaestro/composer-git-hooks --ansi'),
            $this->callShellExec('./vendor/bin/cghooks update --ansi'),
            $this->callShellExec('php artisan lang:publish --ansi'),
            $this->callShellExec('composer require ronasit/laravel-clerk --ansi'),
            $this->callShellExec('php artisan laravel-clerk:install --ansi'),
            $this->callShellExec('composer require ronasit/laravel-telescope-extension --ansi'),
            $this->callShellExec('php artisan telescope:install --ansi'),
            $this->callShellExec('php artisan vendor:publish --tag=initializator-web-login --force'),
            $this->callShellExec('php artisan migrate --ansi --force'),
        );

        $this->mockNativeFunction(
            'RonasIT\ProjectInitializator\Generators',
            $this->callFileGetContent($this->generateResourcePath('md/readme/README.md'), $this->getReadmeTemplateContent('README.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/RESOURCES_AND_CONTACTS.md'), $this->getReadmeTemplateContent('RESOURCES_AND_CONTACTS.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/RESOURCES.md'), $this->getReadmeTemplateContent('RESOURCES.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/CONTACTS.md'), $this->getReadmeTemplateContent('CONTACTS.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/PREREQUISITES.md'), $this->getReadmeTemplateContent('PREREQUISITES.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/GETTING_STARTED.md'), $this->getReadmeTemplateContent('GETTING_STARTED.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/ENVIRONMENTS.md'), $this->getReadmeTemplateContent('ENVIRONMENTS.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/CREDENTIALS_AND_ACCESS.md'), $this->getReadmeTemplateContent('CREDENTIALS_AND_ACCESS.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/CLERK.md'), $this->getReadmeTemplateContent('CLERK.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/RENOVATE.md'), $this->getReadmeTemplateContent('RENOVATE.md')),

            $this->callFilePutContent('README.md', $this->getFixture('default_readme.md')),
        );

        $this
            ->artisan('init "My App"')
            ->expectsConfirmation('The application name is not in PascalCase, would you like to use MyApp')
            ->expectsQuestion('Please specify: email of code owner / team lead', 'test@example.com')
            ->expectsOutput('Project initialized successfully!')
            ->expectsQuestion('Please enter an application URL', 'https://mysite.com')
            ->expectsQuestion('What type of application will your API serve?', 'Multiplatform')
            ->expectsChoice('Please choose the authentication type', 'clerk', ['clerk', 'none'])
            ->expectsConfirmation('Do you want to generate an admin user?', 'yes')
            ->expectsQuestion('Please enter admin email', 'mail@mail.com')
            ->expectsQuestion('Please enter admin password', '123456')
            ->expectsConfirmation('Do you want to generate a README file?', 'yes')
            ->expectsConfirmation('Do you need a `Resources & Contacts` part?', 'yes')
            ->expectsQuestion(
                'Are you going to use Issue Tracker? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'later',
            )
            ->expectsQuestion(
                'Are you going to use Figma? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'later',
            )
            ->expectsQuestion(
                'Are you going to use Sentry? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'later',
            )
            ->expectsQuestion(
                'Are you going to use DataDog? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'later',
            )
            ->expectsQuestion(
                'Are you going to use ArgoCD? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'later',
            )
            ->expectsQuestion(
                'Are you going to use Laravel Telescope? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'later',
            )
            ->expectsQuestion(
                'Are you going to use Laravel Nova? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'later',
            )
            ->expectsQuestion('Please enter a Manager\'s email', '')
            ->expectsConfirmation('Do you need a `Prerequisites` part?', 'yes')
            ->expectsConfirmation('Do you need a `Getting Started` part?', 'yes')
            ->expectsConfirmation('Do you need an `Environments` part?', 'yes')
            ->expectsConfirmation('Do you need a `Credentials and Access` part?', 'yes')
            ->expectsConfirmation('Is Laravel Telescope\'s admin the same as default one?', 'yes')
            ->expectsConfirmation('Is Laravel Nova\'s admin the same as default one?')
            ->expectsQuestion('Please enter admin email for Laravel Nova', 'mail@mail.com')
            ->expectsQuestion('Please enter admin password for Laravel Nova', '123456')
            ->expectsConfirmation('Will project work with media files? (upload, store and return content)')
            ->expectsConfirmation('Would you use Renovate dependabot?', 'yes')
            ->expectsQuestion('Please specify: username of the project reviewer', 'reviewer')
            ->expectsOutput('README generated successfully!')
            ->expectsOutput('Don`t forget to fill the following empty values:')
            ->expectsOutput('- Issue Tracker link')
            ->expectsOutput('- Figma link')
            ->expectsOutput('- Sentry link')
            ->expectsOutput('- DataDog link')
            ->expectsOutput('- ArgoCD link')
            ->expectsOutput('- Manager\'s email')
            ->expectsConfirmation('Do you want to uninstall project-initializator package?')
            ->assertExitCode(0);
    }

    public function testRunWithAdminAndPartialReadmeCreation()
    {
        $this->mockNativeFunction(
            '\Winter\LaravelConfigWriter',
            $this->changeEnvFileCall('.env.example', 'env.example.yml', 'env.example_app_name_not_pascal_case.yml'),
            $this->changeEnvFileCall('.env.development', 'env.development.yml', 'env.development_app_name_not_pascal_case.yml'),
            $this->changeConfigFileCall('config/auto-doc.php', 'auto_doc.php', 'auto_doc_after_changes.php'),
            $this->changeConfigFileCall('config/telescope.php', 'telescope_config.php', 'telescope_config_after_initialization.php'),
        );

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('bootstrap/app.php', $this->getFixture('app.php')),
            $this->callFilePutContent('bootstrap/app.php', $this->getFixture('app_after_changes.php')),
        );

        $this->mockNativeFunction(
            'RonasIT\ProjectInitializator\Commands',
            $this->callFileExists('.env', false),
            $this->callFileExists('.env.development'),

            $this->callCopy('.env.example', '.env'),

            $this->callFileGetContent(base_path('composer.json'), $this->getFixture('composer_with_pint_settings.json')),

            $this->callGlob(base_path('database/migrations/*_roles_create_table.php'), []),
            $this->callGlob(base_path('database/migrations/*_create_roles_table.php'), []),
            $this->callFilePutContent('database/migrations/2018_11_11_111112_roles_create_table.php', $this->getFixture('roles_create_table_migration.php')),
            $this->callFilePutContent('database/migrations/2018_11_11_111113_users_add_role_id.php', $this->getFixture('users_add_role_id_migration.php')),
            $this->callFilePutContent(base_path('composer.json'), $this->getFixture('composer_with_pint_settings.json')),
            $this->callFilePutContent(base_path('/routes/web.php'), "\nAuth::routes();\n", FILE_APPEND),

            $this->callShellExec('composer require laravel/ui --ansi'),
            $this->callShellExec('composer require ronasit/laravel-helpers --ansi'),
            $this->callShellExec('composer require ronasit/laravel-swagger --ansi'),
            $this->callShellExec('php artisan vendor:publish --provider="RonasIT\AutoDoc\AutoDocServiceProvider" --ansi'),
            $this->callShellExec('composer require --dev ronasit/laravel-entity-generator --ansi'),
            $this->callShellExec('composer require --dev laravel/pint --ansi'),
            $this->callShellExec('php artisan vendor:publish --tag=pint-config --ansi'),
            $this->callShellExec('composer require --dev brainmaestro/composer-git-hooks --ansi'),
            $this->callShellExec('./vendor/bin/cghooks update --ansi'),
            $this->callShellExec('php artisan lang:publish --ansi'),
            $this->callShellExec('composer require ronasit/laravel-telescope-extension --ansi'),
            $this->callShellExec('php artisan telescope:install --ansi'),
            $this->callShellExec('php artisan vendor:publish --tag=initializator-web-login --force'),
            $this->callShellExec('php artisan migrate --ansi --force'),
        );

        $this->mockNativeFunction(
            'RonasIT\ProjectInitializator\Generators',
            $this->callFileGetContent($this->generateResourcePath('md/readme/README.md'), $this->getReadmeTemplateContent('README.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/RESOURCES_AND_CONTACTS.md'), $this->getReadmeTemplateContent('RESOURCES_AND_CONTACTS.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/RESOURCES.md'), $this->getReadmeTemplateContent('RESOURCES.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/CONTACTS.md'), $this->getReadmeTemplateContent('CONTACTS.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/ENVIRONMENTS.md'), $this->getReadmeTemplateContent('ENVIRONMENTS.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/CREDENTIALS_AND_ACCESS.md'), $this->getReadmeTemplateContent('CREDENTIALS_AND_ACCESS.md')),
            $this->callFilePutContent('README.md', $this->getFixture('partial_readme.md')),
        );

        $this
            ->artisan('init "My App"')
            ->expectsConfirmation('The application name is not in PascalCase, would you like to use MyApp')
            ->expectsQuestion('Please specify: email of code owner / team lead', 'test@example.com')
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
                'later',
            )
            ->expectsQuestion(
                'Are you going to use Figma? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'no',
            )
            ->expectsQuestion(
                'Are you going to use Sentry? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'no',
            )
            ->expectsQuestion(
                'Are you going to use DataDog? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'no',
            )
            ->expectsQuestion(
                'Are you going to use ArgoCD? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'no',
            )
            ->expectsQuestion(
                'Are you going to use Laravel Telescope? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'no',
            )
            ->expectsQuestion(
                'Are you going to use Laravel Nova? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'no',
            )
            ->expectsQuestion('Please enter a Manager\'s email', 'manager@mail.com')
            ->expectsConfirmation('Do you need a `Prerequisites` part?')
            ->expectsConfirmation('Do you need a `Getting Started` part?')
            ->expectsConfirmation('Do you need an `Environments` part?', 'yes')
            ->expectsConfirmation('Do you need a `Credentials and Access` part?', 'yes')
            ->expectsConfirmation('Will project work with media files? (upload, store and return content)')
            ->expectsConfirmation('Would you use Renovate dependabot?')
            ->expectsOutput('README generated successfully!')
            ->expectsOutput('Don`t forget to fill the following empty values:')
            ->expectsOutput('- Issue Tracker link')
            ->expectsConfirmation('Do you want to uninstall project-initializator package?')
            ->assertExitCode(0);
    }

    public function testRunWithAdminAndFullReadmeCreationAndRemovingInitializatorInstallationMedia()
    {
        $this->mockNativeFunction(
            '\Winter\LaravelConfigWriter',
            $this->changeEnvFileCall('.env.example', 'env.example.yml', 'env.example_app_name_not_pascal_case.yml'),
            $this->changeEnvFileCall('.env.development', 'env.development.yml', 'env.development_app_name_not_pascal_case.yml'),
            $this->changeConfigFileCall('config/auto-doc.php', 'auto_doc.php', 'auto_doc_after_changes.php'),
            $this->changeConfigFileCall('config/telescope.php', 'telescope_config.php', 'telescope_config_after_initialization.php'),
        );

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('bootstrap/app.php', $this->getFixture('app.php')),
            $this->callFilePutContent('bootstrap/app.php', $this->getFixture('app_after_changes.php')),
        );

        $this->mockNativeFunction(
            'RonasIT\ProjectInitializator\Commands',
            $this->callFileExists('.env', false),
            $this->callFileExists('.env.development'),

            $this->callCopy('.env.example', '.env'),

            $this->callFileGetContent(base_path('composer.json'), $this->getFixture('composer_with_pint_settings.json')),

            $this->callGlob(base_path('database/migrations/*_roles_create_table.php'), []),
            $this->callGlob(base_path('database/migrations/*_create_roles_table.php'), []),
            $this->callFilePutContent('database/migrations/2018_11_11_111112_roles_create_table.php', $this->getFixture('roles_create_table_migration.php')),
            $this->callFilePutContent('database/migrations/2018_11_11_111113_users_add_role_id.php', $this->getFixture('users_add_role_id_migration.php')),
            $this->callFilePutContent('database/migrations/2018_11_11_111114_add_default_admin.php', $this->getFixture('migration.php')),
            $this->callFilePutContent('database/migrations/2018_11_11_111115_add_nova_admin.php', $this->getFixture('nova_users_table_migration.php')),
            $this->callFilePutContent('renovate.json', $this->getFixture('renovate.json')),
            $this->callFilePutContent(base_path('composer.json'), $this->getFixture('composer_with_pint_settings.json')),
            $this->callFilePutContent(base_path('/routes/web.php'), "\nAuth::routes();\n", FILE_APPEND),

            $this->callShellExec('git ls-remote --get-url origin', 'https://github.com/ronasit/laravel-helpers.git'),
            $this->callShellExec('composer require laravel/ui --ansi'),
            $this->callShellExec('composer require ronasit/laravel-helpers --ansi'),
            $this->callShellExec('composer require ronasit/laravel-swagger --ansi'),
            $this->callShellExec('php artisan vendor:publish --provider="RonasIT\AutoDoc\AutoDocServiceProvider" --ansi'),
            $this->callShellExec('composer require --dev ronasit/laravel-entity-generator --ansi'),
            $this->callShellExec('composer require --dev laravel/pint --ansi'),
            $this->callShellExec('php artisan vendor:publish --tag=pint-config --ansi'),
            $this->callShellExec('composer require --dev brainmaestro/composer-git-hooks --ansi'),
            $this->callShellExec('./vendor/bin/cghooks update --ansi'),
            $this->callShellExec('php artisan lang:publish --ansi'),
            $this->callShellExec('composer require ronasit/laravel-media --ansi'),
            $this->callShellExec('composer require ronasit/laravel-telescope-extension --ansi'),
            $this->callShellExec('php artisan telescope:install --ansi'),
            $this->callShellExec('php artisan vendor:publish --tag=initializator-web-login --force'),
            $this->callShellExec('composer remove --dev ronasit/laravel-project-initializator --ansi'),
            $this->callShellExec('php artisan migrate --ansi --force'),
        );

        $this->mockNativeFunction(
            'RonasIT\ProjectInitializator\Generators',
            $this->callFileGetContent($this->generateResourcePath('md/readme/README.md'), $this->getReadmeTemplateContent('README.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/RESOURCES_AND_CONTACTS.md'), $this->getReadmeTemplateContent('RESOURCES_AND_CONTACTS.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/RESOURCES.md'), $this->getReadmeTemplateContent('RESOURCES.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/CONTACTS.md'), $this->getReadmeTemplateContent('CONTACTS.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/PREREQUISITES.md'), $this->getReadmeTemplateContent('PREREQUISITES.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/GETTING_STARTED.md'), $this->getReadmeTemplateContent('GETTING_STARTED.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/ENVIRONMENTS.md'), $this->getReadmeTemplateContent('ENVIRONMENTS.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/CREDENTIALS_AND_ACCESS.md'), $this->getReadmeTemplateContent('CREDENTIALS_AND_ACCESS.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/RENOVATE.md'), $this->getReadmeTemplateContent('RENOVATE.md')),
            $this->callFilePutContent('README.md', $this->getFixture('full_readme.md')),
        );

        $this
            ->artisan('init "My App"')
            ->expectsConfirmation('The application name is not in PascalCase, would you like to use MyApp')
            ->expectsQuestion('Please specify: email of code owner / team lead', 'test@example.com')
            ->expectsOutput('Project initialized successfully!')
            ->expectsQuestion('Please enter an application URL', 'https://mysite.com')
            ->expectsQuestion('What type of application will your API serve?', 'Mobile')
            ->expectsChoice('Please choose the authentication type', 'none', ['clerk', 'none'])
            ->expectsConfirmation('Do you want to generate an admin user?', 'yes')
            ->expectsQuestion('Please enter admin email', 'mail@mail.com')
            ->expectsQuestion('Please enter admin password', '123456')
            ->expectsQuestion('Please enter admin name', 'TestAdmin')
            ->expectsQuestion('Please enter admin role id', 1)
            ->expectsConfirmation('Do you want to generate a README file?', 'yes')
            ->expectsConfirmation('Do you need a `Resources & Contacts` part?', 'yes')
            ->expectsQuestion(
                'Are you going to use Issue Tracker? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'https://gitlab.com/my-project',
            )
            ->expectsQuestion(
                'Are you going to use Figma? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'https://figma.com/my-project',
            )
            ->expectsQuestion(
                'Are you going to use Sentry? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'https://sentry.com/my-project',
            )
            ->expectsQuestion(
                'Are you going to use DataDog? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'https://datadoghq.com/my-project',
            )
            ->expectsQuestion(
                'Are you going to use ArgoCD? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'https://argocd.com/my-project',
            )
            ->expectsQuestion(
                'Are you going to use Laravel Telescope? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'https://mypsite.com/telescope-link',
            )
            ->expectsQuestion(
                'Are you going to use Laravel Nova? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'https://mypsite.com/nova-link',
            )
            ->expectsQuestion('Please enter a Manager\'s email', 'manager@mail.com')
            ->expectsConfirmation('Do you need a `Prerequisites` part?', 'yes')
            ->expectsConfirmation('Do you need a `Getting Started` part?', 'yes')
            ->expectsConfirmation('Do you need an `Environments` part?', 'yes')
            ->expectsConfirmation('Do you need a `Credentials and Access` part?', 'yes')
            ->expectsConfirmation('Is Laravel Telescope\'s admin the same as default one?', 'yes')
            ->expectsConfirmation('Is Laravel Nova\'s admin the same as default one?')
            ->expectsQuestion('Please enter admin email for Laravel Nova', 'nova_mail@mail.com')
            ->expectsQuestion('Please enter admin password for Laravel Nova', '654321')
            ->expectsQuestion('Please enter admin name for Laravel Nova', 'Nova Admin')
            ->expectsQuestion('Please enter admin role id for Laravel Nova', 1)
            ->expectsOutput('README generated successfully!')
            ->expectsConfirmation('Will project work with media files? (upload, store and return content)', 'yes')
            ->expectsChoice('Which storage will be used for media files?', 's3', ['gcs', 'local', 's3'])
            ->expectsConfirmation('Would you use Renovate dependabot?', 'yes')
            ->expectsQuestion('Please specify: username of the project reviewer', 'reviewer')
            ->expectsConfirmation('Do you want to uninstall project-initializator package?', 'yes')
            ->assertExitCode(0);
    }

    public function testRunWithoutAdminAndUsingTelescope()
    {
        $this->mockNativeFunction(
            '\Winter\LaravelConfigWriter',
            $this->changeEnvFileCall('.env.example', 'env.example.yml', 'env.example_app_name_not_pascal_case.yml'),
            $this->changeEnvFileCall('.env.development', 'env.development.yml', 'env.development_app_name_not_pascal_case.yml'),
            $this->changeConfigFileCall('config/auto-doc.php', 'auto_doc.php', 'auto_doc_after_changes.php'),
            $this->changeConfigFileCall('config/telescope.php', 'telescope_config.php', 'telescope_config_after_initialization.php'),
        );

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('bootstrap/app.php', $this->getFixture('app.php')),
            $this->callFilePutContent('bootstrap/app.php', $this->getFixture('app_after_changes.php')),
        );

        $this->mockNativeFunction(
            'RonasIT\ProjectInitializator\Commands',
            $this->callFileExists('.env', false),
            $this->callFileExists('.env.development'),

            $this->callCopy('.env.example', '.env'),

            $this->callFileGetContent(base_path('composer.json'), $this->getFixture('composer_with_pint_settings.json')),

            $this->callGlob(base_path('database/migrations/*_roles_create_table.php'), []),
            $this->callGlob(base_path('database/migrations/*_create_roles_table.php'), []),
            $this->callFilePutContent('database/migrations/2018_11_11_111112_roles_create_table.php', $this->getFixture('roles_create_table_migration.php')),
            $this->callFilePutContent('database/migrations/2018_11_11_111113_users_add_role_id.php', $this->getFixture('users_add_role_id_migration.php')),
            $this->callFilePutContent('database/migrations/2018_11_11_111114_add_telescope_admin.php', $this->getFixture('telescope_users_table_migration.php')),
            $this->callFilePutContent('database/migrations/2018_11_11_111115_add_nova_admin.php', $this->getFixture('nova_users_table_migration.php')),
            $this->callFilePutContent(base_path('composer.json'), $this->getFixture('composer_with_pint_settings.json')),
            $this->callFilePutContent(base_path('/routes/web.php'), "\nAuth::routes();\n", FILE_APPEND),

            $this->callShellExec('composer require laravel/ui --ansi'),
            $this->callShellExec('composer require ronasit/laravel-helpers --ansi'),
            $this->callShellExec('composer require ronasit/laravel-swagger --ansi'),
            $this->callShellExec('php artisan vendor:publish --provider="RonasIT\AutoDoc\AutoDocServiceProvider" --ansi'),
            $this->callShellExec('composer require --dev ronasit/laravel-entity-generator --ansi'),
            $this->callShellExec('composer require --dev laravel/pint --ansi'),
            $this->callShellExec('php artisan vendor:publish --tag=pint-config --ansi'),
            $this->callShellExec('composer require --dev brainmaestro/composer-git-hooks --ansi'),
            $this->callShellExec('./vendor/bin/cghooks update --ansi'),
            $this->callShellExec('php artisan lang:publish --ansi'),
            $this->callShellExec('composer require ronasit/laravel-telescope-extension --ansi'),
            $this->callShellExec('php artisan telescope:install --ansi'),
            $this->callShellExec('php artisan vendor:publish --tag=initializator-web-login --force'),
            $this->callShellExec('php artisan migrate --ansi --force'),
        );

        $this->mockNativeFunction(
            'RonasIT\ProjectInitializator\Generators',
            $this->callFileGetContent($this->generateResourcePath('md/readme/README.md'), $this->getReadmeTemplateContent('README.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/RESOURCES_AND_CONTACTS.md'), $this->getReadmeTemplateContent('RESOURCES_AND_CONTACTS.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/RESOURCES.md'), $this->getReadmeTemplateContent('RESOURCES.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/CONTACTS.md'), $this->getReadmeTemplateContent('CONTACTS.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/ENVIRONMENTS.md'), $this->getReadmeTemplateContent('ENVIRONMENTS.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/CREDENTIALS_AND_ACCESS.md'), $this->getReadmeTemplateContent('CREDENTIALS_AND_ACCESS.md')),
            $this->callFilePutContent('README.md', $this->getFixture('partial_readme_with_telescope.md')),
        );

        $this
            ->artisan('init "My App"')
            ->expectsConfirmation('The application name is not in PascalCase, would you like to use MyApp')
            ->expectsQuestion('Please specify: email of code owner / team lead', 'test@example.com')
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
                'later',
            )
            ->expectsQuestion(
                'Are you going to use Figma? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'no',
            )
            ->expectsQuestion(
                'Are you going to use Sentry? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'no',
            )
            ->expectsQuestion(
                'Are you going to use DataDog? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'no',
            )
            ->expectsQuestion(
                'Are you going to use ArgoCD? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'no',
            )
            ->expectsQuestion(
                'Are you going to use Laravel Telescope? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'later',
            )
            ->expectsQuestion(
                'Are you going to use Laravel Nova? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'later',
            )
            ->expectsQuestion('Please enter a Manager\'s email', 'manager@mail.com')
            ->expectsConfirmation('Do you need a `Prerequisites` part?')
            ->expectsConfirmation('Do you need a `Getting Started` part?')
            ->expectsConfirmation('Do you need an `Environments` part?', 'yes')
            ->expectsConfirmation('Do you need a `Credentials and Access` part?', 'yes')
            ->expectsQuestion('Please enter admin email for Laravel Telescope', 'telescope_mail@mail.com')
            ->expectsQuestion('Please enter admin password for Laravel Telescope', '654321')
            ->expectsQuestion('Please enter admin name for Laravel Telescope', 'Telescope Admin')
            ->expectsQuestion('Please enter admin role id for Laravel Telescope', 1)
            ->expectsQuestion('Please enter admin email for Laravel Nova', 'nova_mail@mail.com')
            ->expectsQuestion('Please enter admin password for Laravel Nova', '654321')
            ->expectsQuestion('Please enter admin name for Laravel Nova', 'Nova Admin')
            ->expectsQuestion('Please enter admin role id for Laravel Nova', 1)
            ->expectsConfirmation('Will project work with media files? (upload, store and return content)')
            ->expectsConfirmation('Would you use Renovate dependabot?')
            ->expectsOutput('README generated successfully!')
            ->expectsOutput('Don`t forget to fill the following empty values:')
            ->expectsOutput('- Issue Tracker link')
            ->expectsConfirmation('Do you want to uninstall project-initializator package?')
            ->assertExitCode(0);
    }

    public function testRunWithClerkMobileAppWithPintInstalled(): void
    {
        $this->mockNativeFunction(
            '\Winter\LaravelConfigWriter',
            $this->changeEnvFileCall('.env.example', 'env.example.yml', 'env.example_app_name_not_pascal_case.yml'),
            $this->changeEnvFileCall('.env.development', 'env.development.yml', 'env.development_app_name_not_pascal_case.yml'),
            $this->changeEnvFileCall('.env', 'env.example_app_name_not_pascal_case.yml', 'env.example_clerk_credentials_added_mobile_app.yml'),
            $this->changeEnvFileCall('.env.example', 'env.example_app_name_not_pascal_case.yml', 'env.example_clerk_credentials_added_mobile_app.yml'),
            $this->changeEnvFileCall('.env.development', 'env.development_app_name_not_pascal_case.yml', 'env.development_clerk_credentials_added_mobile_app.yml'),
            $this->changeConfigFileCall('config/auto-doc.php', 'auto_doc.php', 'auto_doc_after_changes.php'),
            $this->changeConfigFileCall('config/telescope.php', 'telescope_config.php', 'telescope_config_after_initialization.php'),
        );

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent(app_path('Models/User.php'), $this->getFixture('user_model.php')),
            $this->callFilePutContent(app_path('Models/User.php'), $this->getFixture('user_model_fillable_updated.php')),
            $this->callFileGetContent('bootstrap/app.php', $this->getFixture('app.php')),
            $this->callFilePutContent('bootstrap/app.php', $this->getFixture('app_after_changes.php')),
        );

        $this->mockNativeFunction(
            'RonasIT\ProjectInitializator\Commands',
            $this->callFileExists('.env', false),
            $this->callFileExists('.env.development'),

            $this->callCopy('.env.example', '.env'),

            $this->callFileGetContent(base_path('composer.json'), $this->getFixture('composer_with_pint_settings.json')),

            $this->callFilePutContent('database/migrations/2018_11_11_111112_users_format_to_clerk.php', $this->getFixture('users_format_to_clerk_migration.php')),
            $this->callFilePutContent('app/Support/Clerk/ClerkUserRepository.php', $this->getFixture('clerk_user_repository.php')),
            $this->callFilePutContent('database/migrations/2018_11_11_111113_admins_create_table.php', $this->getFixture('admins_table_migration.php')),
            $this->callFilePutContent('database/migrations/2018_11_11_111114_add_default_admin.php', $this->getFixture('admins_add_default_admin.php')),
            $this->callFilePutContent('renovate.json', $this->getFixture('renovate.json')),
            $this->callFilePutContent(base_path('composer.json'), $this->getFixture('composer_with_pint_settings.json')),
            $this->callFilePutContent(base_path('/routes/web.php'), "\nAuth::routes();\n", FILE_APPEND),

            $this->callShellExec('git ls-remote --get-url origin', 'https://github.com/ronasit/laravel-helpers.git'),
            $this->callShellExec('composer require laravel/ui --ansi'),
            $this->callShellExec('composer require ronasit/laravel-helpers --ansi'),
            $this->callShellExec('composer require ronasit/laravel-swagger --ansi'),
            $this->callShellExec('php artisan vendor:publish --provider="RonasIT\AutoDoc\AutoDocServiceProvider" --ansi'),
            $this->callShellExec('composer require --dev ronasit/laravel-entity-generator --ansi'),
            $this->callShellExec('composer require --dev laravel/pint --ansi'),
            $this->callShellExec('php artisan vendor:publish --tag=pint-config --ansi'),
            $this->callShellExec('composer require --dev brainmaestro/composer-git-hooks --ansi'),
            $this->callShellExec('./vendor/bin/cghooks update --ansi'),
            $this->callShellExec('php artisan lang:publish --ansi'),
            $this->callShellExec('composer require ronasit/laravel-clerk --ansi'),
            $this->callShellExec('php artisan laravel-clerk:install --ansi'),
            $this->callShellExec('composer require ronasit/laravel-telescope-extension --ansi'),
            $this->callShellExec('php artisan telescope:install --ansi'),
            $this->callShellExec('php artisan vendor:publish --tag=initializator-web-login --force'),
            $this->callShellExec('php artisan migrate --ansi --force'),
        );

        $this->mockNativeFunction(
            'RonasIT\ProjectInitializator\Generators',
            $this->callFileGetContent($this->generateResourcePath('md/readme/README.md'), $this->getReadmeTemplateContent('README.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/RESOURCES_AND_CONTACTS.md'), $this->getReadmeTemplateContent('RESOURCES_AND_CONTACTS.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/RESOURCES.md'), $this->getReadmeTemplateContent('RESOURCES.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/CONTACTS.md'), $this->getReadmeTemplateContent('CONTACTS.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/PREREQUISITES.md'), $this->getReadmeTemplateContent('PREREQUISITES.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/GETTING_STARTED.md'), $this->getReadmeTemplateContent('GETTING_STARTED.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/ENVIRONMENTS.md'), $this->getReadmeTemplateContent('ENVIRONMENTS.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/CREDENTIALS_AND_ACCESS.md'), $this->getReadmeTemplateContent('CREDENTIALS_AND_ACCESS.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/CLERK.md'), $this->getReadmeTemplateContent('CLERK.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/RENOVATE.md'), $this->getReadmeTemplateContent('RENOVATE.md')),

            $this->callFilePutContent('README.md', $this->getFixture('default_readme_with_mobile_app.md')),
        );

        $this
            ->artisan('init "My App"')
            ->expectsConfirmation('The application name is not in PascalCase, would you like to use MyApp')
            ->expectsQuestion('Please specify: email of code owner / team lead', 'test@example.com')
            ->expectsOutput('Project initialized successfully!')
            ->expectsQuestion('Please enter an application URL', 'https://mysite.com')
            ->expectsQuestion('What type of application will your API serve?', 'Mobile')
            ->expectsChoice('Please choose the authentication type', 'clerk', ['clerk', 'none'])
            ->expectsConfirmation('Do you want to generate an admin user?', 'yes')
            ->expectsQuestion('Please enter admin email', 'mail@mail.com')
            ->expectsQuestion('Please enter admin password', '123456')
            ->expectsConfirmation('Do you want to generate a README file?', 'yes')
            ->expectsConfirmation('Do you need a `Resources & Contacts` part?', 'yes')
            ->expectsQuestion(
                'Are you going to use Issue Tracker? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'later',
            )
            ->expectsQuestion(
                'Are you going to use Figma? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'later',
            )
            ->expectsQuestion(
                'Are you going to use Sentry? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'later',
            )
            ->expectsQuestion(
                'Are you going to use DataDog? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'later',
            )
            ->expectsQuestion(
                'Are you going to use ArgoCD? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'later',
            )
            ->expectsQuestion(
                'Are you going to use Laravel Telescope? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'later',
            )
            ->expectsQuestion(
                'Are you going to use Laravel Nova? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'later',
            )
            ->expectsQuestion('Please enter a Manager\'s email', '')
            ->expectsConfirmation('Do you need a `Prerequisites` part?', 'yes')
            ->expectsConfirmation('Do you need a `Getting Started` part?', 'yes')
            ->expectsConfirmation('Do you need an `Environments` part?', 'yes')
            ->expectsConfirmation('Do you need a `Credentials and Access` part?', 'yes')
            ->expectsConfirmation('Is Laravel Telescope\'s admin the same as default one?', 'yes')
            ->expectsConfirmation('Is Laravel Nova\'s admin the same as default one?', 'yes')
            ->expectsConfirmation('Will project work with media files? (upload, store and return content)')
            ->expectsConfirmation('Would you use Renovate dependabot?', 'yes')
            ->expectsQuestion('Please specify: username of the project reviewer', 'reviewer')
            ->expectsOutput('README generated successfully!')
            ->expectsOutput('Don`t forget to fill the following empty values:')
            ->expectsOutput('- Issue Tracker link')
            ->expectsOutput('- Figma link')
            ->expectsOutput('- Sentry link')
            ->expectsOutput('- DataDog link')
            ->expectsOutput('- ArgoCD link')
            ->expectsOutput('- Manager\'s email')
            ->expectsConfirmation('Do you want to uninstall project-initializator package?')
            ->assertExitCode(0);
    }

    public function testRunWithClerkAdditionalAdminsWithoutDefaultAdmin(): void
    {
        $this->mockNativeFunction(
            '\Winter\LaravelConfigWriter',
            $this->changeEnvFileCall('.env.example', 'env.example.yml', 'env.example_app_name_not_pascal_case.yml'),
            $this->changeEnvFileCall('.env.development', 'env.development.yml', 'env.development_app_name_not_pascal_case.yml'),
            $this->changeEnvFileCall('.env', 'env.example.yml', 'env.example_clerk_credentials_added.yml'),
            $this->changeEnvFileCall('.env.example', 'env.example.yml', 'env.example_clerk_credentials_added.yml'),
            $this->changeEnvFileCall('.env.development', 'env.development_app_name_not_pascal_case.yml', 'env.development_clerk_credentials_added.yml'),
            $this->changeConfigFileCall('config/auto-doc.php', 'auto_doc.php', 'auto_doc_after_changes.php'),
            $this->changeConfigFileCall('config/telescope.php', 'telescope_config.php', 'telescope_config_after_initialization.php'),
        );

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent(app_path('Models/User.php'), $this->getFixture('user_model.php')),
            $this->callFilePutContent(app_path('Models/User.php'), $this->getFixture('user_model_fillable_updated.php')),
            $this->callFileGetContent('bootstrap/app.php', $this->getFixture('app.php')),
            $this->callFilePutContent('bootstrap/app.php', $this->getFixture('app_after_changes.php')),
        );

        $this->mockNativeFunction(
            'RonasIT\ProjectInitializator\Commands',
            $this->callFileExists('.env', false),
            $this->callFileExists('.env.development'),

            $this->callCopy('.env.example', '.env'),

            $this->callFileGetContent(base_path('composer.json'), $this->getFixture('composer_with_pint_settings.json')),

            $this->callFilePutContent('database/migrations/2018_11_11_111112_users_format_to_clerk.php', $this->getFixture('users_format_to_clerk_migration.php')),
            $this->callFilePutContent('app/Support/Clerk/ClerkUserRepository.php', $this->getFixture('clerk_user_repository.php')),
            $this->callGlob(base_path('database/migrations/*_admins_create_table.php'), []),
            $this->callFilePutContent('database/migrations/2018_11_11_111113_admins_create_table.php', $this->getFixture('admins_table_migration.php')),
            $this->callFilePutContent('database/migrations/2018_11_11_111114_add_telescope_admin.php', $this->getFixture('admins_add_telescope_admin_migration.php')),
            $this->callGlob(base_path('database/migrations/*_admins_create_table.php'), [base_path('database/migrations/2018_11_11_111113_admins_create_table.php')]),
            $this->callFilePutContent('database/migrations/2018_11_11_111115_add_nova_admin.php', $this->getFixture('admins_add_nova_admin_migration.php')),
            $this->callFilePutContent(base_path('composer.json'), $this->getFixture('composer_with_pint_settings.json')),
            $this->callFilePutContent(base_path('/routes/web.php'), "\nAuth::routes();\n", FILE_APPEND),

            $this->callShellExec('composer require laravel/ui --ansi'),
            $this->callShellExec('composer require ronasit/laravel-helpers --ansi'),
            $this->callShellExec('composer require ronasit/laravel-swagger --ansi'),
            $this->callShellExec('php artisan vendor:publish --provider="RonasIT\AutoDoc\AutoDocServiceProvider" --ansi'),
            $this->callShellExec('composer require --dev ronasit/laravel-entity-generator --ansi'),
            $this->callShellExec('composer require --dev laravel/pint --ansi'),
            $this->callShellExec('php artisan vendor:publish --tag=pint-config --ansi'),
            $this->callShellExec('composer require --dev brainmaestro/composer-git-hooks --ansi'),
            $this->callShellExec('./vendor/bin/cghooks update --ansi'),
            $this->callShellExec('php artisan lang:publish --ansi'),
            $this->callShellExec('composer require ronasit/laravel-clerk --ansi'),
            $this->callShellExec('php artisan laravel-clerk:install --ansi'),
            $this->callShellExec('composer require ronasit/laravel-telescope-extension --ansi'),
            $this->callShellExec('php artisan telescope:install --ansi'),
            $this->callShellExec('php artisan vendor:publish --tag=initializator-web-login --force'),
            $this->callShellExec('php artisan migrate --ansi --force'),
        );

        $this->mockNativeFunction(
            'RonasIT\ProjectInitializator\Generators',
            $this->callFileGetContent($this->generateResourcePath('md/readme/README.md'), $this->getReadmeTemplateContent('README.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/RESOURCES_AND_CONTACTS.md'), $this->getReadmeTemplateContent('RESOURCES_AND_CONTACTS.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/RESOURCES.md'), $this->getReadmeTemplateContent('RESOURCES.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/CONTACTS.md'), $this->getReadmeTemplateContent('CONTACTS.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/CREDENTIALS_AND_ACCESS.md'), $this->getReadmeTemplateContent('CREDENTIALS_AND_ACCESS.md')),
            $this->callFileGetContent($this->generateResourcePath('md/readme/CLERK.md'), $this->getReadmeTemplateContent('CLERK.md')),
            $this->callFilePutContent('README.md', $this->getFixture('partial_readme_clerk_with_credentials.md')),
        );

        $this
            ->artisan('init "My App"')
            ->expectsConfirmation('The application name is not in PascalCase, would you like to use MyApp')
            ->expectsQuestion('Please specify: email of code owner / team lead', 'test@example.com')
            ->expectsOutput('Project initialized successfully!')
            ->expectsQuestion('Please enter an application URL', 'https://mysite.com')
            ->expectsQuestion('What type of application will your API serve?', 'Web')
            ->expectsChoice('Please choose the authentication type', 'clerk', ['clerk', 'none'])
            ->expectsConfirmation('Do you want to generate an admin user?')
            ->expectsConfirmation('Do you want to generate a README file?', 'yes')
            ->expectsConfirmation('Do you need a `Resources & Contacts` part?', 'yes')
            ->expectsQuestion(
                'Are you going to use Issue Tracker? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'no',
            )
            ->expectsQuestion(
                'Are you going to use Figma? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'no',
            )
            ->expectsQuestion(
                'Are you going to use Sentry? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'no',
            )
            ->expectsQuestion(
                'Are you going to use DataDog? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'no',
            )
            ->expectsQuestion(
                'Are you going to use ArgoCD? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'no',
            )
            ->expectsQuestion(
                'Are you going to use Laravel Telescope? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'later',
            )
            ->expectsQuestion(
                'Are you going to use Laravel Nova? '
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.',
                'later',
            )
            ->expectsQuestion('Please enter a Manager\'s email', 'manager@mail.com')
            ->expectsConfirmation('Do you need a `Prerequisites` part?')
            ->expectsConfirmation('Do you need a `Getting Started` part?')
            ->expectsConfirmation('Do you need an `Environments` part?')
            ->expectsConfirmation('Do you need a `Credentials and Access` part?', 'yes')
            ->expectsQuestion('Please enter admin email for Laravel Telescope', 'telescope_mail@mail.com')
            ->expectsQuestion('Please enter admin password for Laravel Telescope', '654321')
            ->expectsQuestion('Please enter admin email for Laravel Nova', 'mail@mail.com')
            ->expectsQuestion('Please enter admin password for Laravel Nova', '123456')
            ->expectsOutput('README generated successfully!')
            ->expectsConfirmation('Will project work with media files? (upload, store and return content)')
            ->expectsConfirmation('Would you use Renovate dependabot?')
            ->expectsConfirmation('Do you want to uninstall project-initializator package?')
            ->assertExitCode(0);
    }

    public function testRunWithoutAdminAndWithoutReadmeAndWithMediaStorageGcs()
    {
        $this->mockNativeFunction(
            '\Winter\LaravelConfigWriter',
            $this->changeEnvFileCall('.env.example', 'env.example.yml', 'env.example_app_name_pascal_case.yml'),
            $this->changeEnvFileCall('.env.development', 'env.development.yml', 'env.development_app_name_pascal_case.yml'),
            $this->changeEnvFileCall('.env.development', 'env.development_app_name_pascal_case.yml', 'env.development_storage_gcs.yml'),
            $this->changeConfigFileCall('config/filesystems.php', 'filesystems.php', 'filesystems_after_changes.php'),
            $this->changeConfigFileCall('config/auto-doc.php', 'auto_doc.php', 'auto_doc_after_changes.php'),
            $this->changeConfigFileCall('config/telescope.php', 'telescope_config.php', 'telescope_config_after_initialization.php'),
        );

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('bootstrap/app.php', $this->getFixture('app.php')),
            $this->callFilePutContent('bootstrap/app.php', $this->getFixture('app_after_changes.php')),
        );

        $this->mockNativeFunction(
            'RonasIT\ProjectInitializator\Commands',
            $this->callFileExists('.env', false),
            $this->callFileExists('.env.development', false),

            $this->callCopy('.env.example', '.env'),
            $this->callCopy('.env.example', '.env.development'),

            $this->callClassExists('Laravel\Telescope\TelescopeServiceProvider'),

            $this->callFileGetContent(base_path('composer.json'), $this->getFixture('composer_with_pint_settings.json')),

            $this->callGlob(base_path('database/migrations/*_roles_create_table.php'), []),
            $this->callGlob(base_path('database/migrations/*_create_roles_table.php'), []),
            $this->callFilePutContent('database/migrations/2018_11_11_111112_roles_create_table.php', $this->getFixture('roles_create_table_migration.php')),
            $this->callFilePutContent('database/migrations/2018_11_11_111113_users_add_role_id.php', $this->getFixture('users_add_role_id_migration.php')),
            $this->callFilePutContent(base_path('composer.json'), $this->getFixture('composer_with_pint_settings.json')),
            $this->callFilePutContent(base_path('/routes/web.php'), "\nAuth::routes();\n", FILE_APPEND),

            $this->callShellExec('composer require laravel/ui --ansi'),
            $this->callShellExec('composer require ronasit/laravel-helpers --ansi'),
            $this->callShellExec('composer require ronasit/laravel-swagger --ansi'),
            $this->callShellExec('php artisan vendor:publish --provider="RonasIT\AutoDoc\AutoDocServiceProvider" --ansi'),
            $this->callShellExec('composer require --dev ronasit/laravel-entity-generator --ansi'),
            $this->callShellExec('composer require --dev laravel/pint --ansi'),
            $this->callShellExec('php artisan vendor:publish --tag=pint-config --ansi'),
            $this->callShellExec('composer require --dev brainmaestro/composer-git-hooks --ansi'),
            $this->callShellExec('./vendor/bin/cghooks update --ansi'),
            $this->callShellExec('php artisan lang:publish --ansi'),
            $this->callShellExec('composer require ronasit/laravel-media --ansi'),
            $this->callShellExec('composer require spatie/laravel-google-cloud-storage --ansi'),
            $this->callShellExec('php artisan vendor:publish --tag=initializator-web-login --force'),
            $this->callShellExec('php artisan migrate --ansi --force'),
        );

        $this
            ->artisan('init "My App"')
            ->expectsConfirmation('The application name is not in PascalCase, would you like to use MyApp', 'yes')
            ->expectsQuestion('Please specify: email of code owner / team lead', 'test@example.com')
            ->expectsOutput('Project initialized successfully!')
            ->expectsQuestion('Please enter an application URL', 'https://mysite.com')
            ->expectsQuestion('What type of application will your API serve?', 'Multiplatform')
            ->expectsChoice('Please choose the authentication type', 'none', ['clerk', 'none'])
            ->expectsConfirmation('Do you want to generate an admin user?')
            ->expectsConfirmation('Do you want to generate a README file?')
            ->expectsConfirmation('Will project work with media files? (upload, store and return content)', 'yes')
            ->expectsChoice('Which storage will be used for media files?', 'gcs', ['gcs', 'local', 's3'])
            ->expectsConfirmation('Would you use Renovate dependabot?')
            ->expectsOutput('Don`t forget to fill the following empty values:')
            ->expectsOutput('- GOOGLE_CLOUD_STORAGE_BUCKET')
            ->expectsOutput('- GOOGLE_CLOUD_PROJECT_ID')
            ->expectsConfirmation('Do you want to uninstall project-initializator package?')
            ->assertExitCode(0);
    }

    public function testDefaultAdminsCredentials(): void
    {
        $commandMock = Mockery::mock(InitCommand::class)->shouldAllowMockingProtectedMethods();

        $commandMock->shouldReceive('ask')->andReturnUsing(fn ($question, $default) => $default);
        $commandMock->shouldReceive('publishRoleMigrations');
        $commandMock->shouldReceive('publishAdminMigration')->andReturnNull();

        $this->setReflectionProperty($commandMock, InitCommand::class, 'authType', AuthTypeEnum::None);
        $this->setReflectionProperty($commandMock, InitCommand::class, 'kebabAppName', 'my-app');

        $createAdminMethod = ReflectionMethod::createFromMethodName(InitCommand::class . '::createAdminUser');
        $credentials = $createAdminMethod->invoke($commandMock);

        $this->assertEquals('Admin', $credentials['name']);
    }

    protected function setReflectionProperty(object $object, string $class, string $property, mixed $value): void
    {
        $reflection = new ReflectionProperty($class, $property);
        $reflection->setValue($object, $value);
    }
}
