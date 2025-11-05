<?php

namespace RonasIT\ProjectInitializator\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use RonasIT\ProjectInitializator\Enums\AppTypeEnum;
use RonasIT\ProjectInitializator\Enums\AuthTypeEnum;
use RonasIT\ProjectInitializator\Enums\RoleEnum;
use Winter\LaravelConfigWriter\ArrayFile;
use Winter\LaravelConfigWriter\EnvFile;
use RonasIT\ProjectInitializator\Generators\ReadmeGenerator;

class InitCommand extends Command implements Isolatable
{
    public const array RESOURCES_ITEMS = [
        'issue_tracker' => 'Issue Tracker',
        'figma' => 'Figma',
        'sentry' => 'Sentry',
        'datadog' => 'DataDog',
        'argocd' => 'ArgoCD',
        'telescope' => 'Laravel Telescope',
        'nova' => 'Laravel Nova',
    ];

    public const array CONTACTS_ITEMS = [
        'manager' => 'Manager',
    ];

    public const array CREDENTIALS_ITEMS = [
        'telescope' => 'Laravel Telescope',
        'nova' => 'Laravel Nova',
    ];

    public const array DEFAULT_URLS = [
        'telescope',
        'nova',
    ];

    protected $signature = 'init {application-name : The application name }';

    protected $description = 'Initialize required project parameters to run DEV environment';

    protected string $codeOwnerEmail;

    protected array $resources = [];

    protected array $adminCredentials = [];

    protected AuthTypeEnum $authType;

    protected string $appUrl;

    protected array $emptyValuesList = [];

    protected array $shellCommands = [
        'composer require laravel/ui',
        'composer require ronasit/laravel-helpers',
        'composer require ronasit/laravel-swagger',
        'php artisan vendor:publish --provider="RonasIT\\AutoDoc\\AutoDocServiceProvider"',
        'composer require --dev ronasit/laravel-entity-generator',
        'composer require --dev laravel/pint',
        'php artisan vendor:publish --tag=pint-config',
        'composer require --dev brainmaestro/composer-git-hooks',
        './vendor/bin/cghooks update',
        'php artisan lang:publish',
    ];

    protected bool $shouldUninstallPackage = false;

    protected string $appName;

    protected string $dbConnection = 'pgsql';
    protected string $dbHost = 'pgsql';
    protected string $dbPort = '5432';
    protected string $dbName = 'postgres';
    protected string $dbUserName = 'postgres';

    protected AppTypeEnum $appType;

    public function __construct(
        protected ReadmeGenerator $readmeGenerator,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->prepareAppName();

        $kebabName = Str::kebab($this->appName);

        $this->codeOwnerEmail = $this->validateInput(
            method: fn () => $this->ask('Please specify a Code Owner/Team Lead\'s email'),
            field: 'email of code owner / team lead',
            rules: 'required|email',
        );

        $this->appUrl = $this->ask('Please enter an application URL', "https://api.dev.{$kebabName}.com");

        $envConfig = [
            'APP_NAME' => $this->appName,
            'DB_CONNECTION' => $this->dbConnection,
            'DB_HOST' => $this->dbHost,
            'DB_PORT' => $this->dbPort,
            'DB_DATABASE' => $this->dbName,
            'DB_USERNAME' => $this->dbUserName,
            'DB_PASSWORD' => '',
        ];

        $this->updateEnvFile('.env.example', $envConfig);

        if (!file_exists('.env')) {
            copy('.env.example', '.env');
        } else {
            $this->updateEnvFile('.env', $envConfig);
        }

        if (!file_exists('.env.development')) {
            copy('.env.example', '.env.development');
        }

        $this->updateEnvFile('.env.development', [
            'APP_NAME' => $this->appName,
            'APP_URL' => $this->appUrl,
            'APP_MAINTENANCE_DRIVER' => 'cache',
            'CACHE_STORE' => 'redis',
            'QUEUE_CONNECTION' => 'redis',
            'SESSION_DRIVER' => 'redis',
            'DB_CONNECTION' => $this->dbConnection,
            'DB_HOST' => '',
            'DB_PORT' => '',
            'DB_DATABASE' => '',
            'DB_USERNAME' => '',
            'DB_PASSWORD' => '',
        ]);

        $this->info('Project initialized successfully!');

        $this->appType = AppTypeEnum::from(
            $this->choice(
                question: 'What type of application will your API serve?',
                choices: AppTypeEnum::values(),
                default: AppTypeEnum::Multiplatform->value,
            ),
        );

        $this->authType = AuthTypeEnum::from($this->choice(
            question: 'Please choose the authentication type',
            choices: AuthTypeEnum::values(),
            default: AuthTypeEnum::None->value,
        ));

        if ($this->authType === AuthTypeEnum::Clerk) {
            $this->enableClerk();

            $data = [
                'AUTH_GUARD' => 'clerk',
                'CLERK_ALLOWED_ISSUER' => '',
                'CLERK_SECRET_KEY' => '',
                'CLERK_SIGNER_KEY_PATH' => '',
            ];

            if ($this->appType !== AppTypeEnum::Mobile) {
                $data['CLERK_ALLOWED_ORIGINS'] = '';
            }

            $this->updateEnvFile('.env', $data);
            $this->updateEnvFile('.env.example', $data);
            $this->updateEnvFile('.env.development', $data);
        }

        if ($this->confirm('Do you want to generate an admin user?', true)) {
            if ($this->authType === AuthTypeEnum::Clerk) {
                $this->publishAdminsTableMigration();
            }

            $this->createAdminUser($kebabName);
        }

        if ($shouldGenerateReadme = $this->confirm('Do you want to generate a README file?', true)) {
            $this->readmeGenerator->generate($this->appName, $this->appType->value, $this->appUrl);

            if ($this->confirm('Do you need a `Resources & Contacts` part?', true)) {
                $this->readmeGenerator->fillResourcesAndContacts();
                $this->fillResources();
                $this->fillContacts();
            }

            if ($this->confirm('Do you need a `Prerequisites` part?', true)) {
                $this->readmeGenerator->fillPrerequisites();
            }

            if ($this->confirm('Do you need a `Getting Started` part?', true)) {
                $this->fillGettingStarted();
            }

            if ($this->confirm('Do you need an `Environments` part?', true)) {
                $this->readmeGenerator->fillEnvironments();
            }

            if ($this->confirm('Do you need a `Credentials and Access` part?', true)) {
                $this->fillCredentialsAndAccess($kebabName);

                if ($this->authType === AuthTypeEnum::Clerk) {
                    $this->readmeGenerator->fillClerkAuth();
                }
            }
        }

        if ($this->confirm('Would you use Renovate dependabot?', true)) {
            $this->saveRenovateJSON();

            if ($shouldGenerateReadme) {
                $this->readmeGenerator->fillRenovate();
            }
        }

        if ($shouldGenerateReadme) {
            $this->readmeGenerator->save();

            $this->info('README generated successfully!');

            if ($this->emptyValuesList) {
                $this->warn('Don`t forget to fill the following empty values:');

                foreach ($this->emptyValuesList as $value) {
                    $this->warn("- {$value}");
                }
            }
        }

        if (!class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            array_push(
                $this->shellCommands,
                'composer require ronasit/laravel-telescope-extension',
                'php artisan telescope:install',
            );
        }

        if ($this->confirm('Do you want to install media package?')) {
            $this->shellCommands[] = 'composer require ronasit/laravel-media';
        }

        if ($this->confirm('Do you want to uninstall project-initializator package?', true)) {
            $this->shouldUninstallPackage = true;
        }

        $this->setupComposerHooks();

        $this->setAutoDocContactEmail($this->codeOwnerEmail);

        foreach ($this->shellCommands as $shellCommand) {
            shell_exec("{$shellCommand} --ansi");
        }

        $this->changeMiddlewareForTelescopeAuthorization();

        $this->publishWebLogin();

        if ($this->shouldUninstallPackage) {
            shell_exec('composer remove --dev ronasit/laravel-project-initializator --ansi');
        }

        $this->runMigrations();
    }

    protected function fillResources(): void
    {
        $filePart = $this->readmeGenerator->loadReadmePart('RESOURCES.md');
        $laterText = '(will be added later)';

        foreach (self::RESOURCES_ITEMS as $key => $title) {
            $defaultAnswer = (in_array($key, self::DEFAULT_URLS)) ? $this->appUrl . "/{$key}" : 'later';
            $text = "Are you going to use {$title}? "
                . "Please enter a link or select `later` to do it later, otherwise select `no`.";

            $link = $this->anticipate(
                $text,
                ['later', 'no'],
                $defaultAnswer
            );

            if ($link === 'later') {
                $this->emptyValuesList[] = "{$title} link";
                $this->readmeGenerator->setReadmeValue($filePart, "{$key}_link");
                $this->readmeGenerator->setReadmeValue($filePart, "{$key}_later", $laterText);
            } elseif ($link !== 'no') {
                $this->readmeGenerator->setReadmeValue($filePart, "{$key}_link", $link);
                $this->readmeGenerator->setReadmeValue($filePart, "{$key}_later");
            }

            $this->resources[$key] = ($link !== 'no');

            $this->readmeGenerator->removeTag($filePart, $key, $link === 'no');
        }

        $this->readmeGenerator->setReadmeValue($filePart, 'api_link', $this->appUrl);
        $this->readmeGenerator->updateReadmeFile($filePart);
    }

    protected function fillContacts(): void
    {
        $filePart = $this->readmeGenerator->loadReadmePart('CONTACTS.md');

        foreach (self::CONTACTS_ITEMS as $key => $title) {
            if ($link = $this->ask("Please enter a {$title}'s email", '')) {
                $this->readmeGenerator->setReadmeValue($filePart, "{$key}_link", $link);
            } else {
                $this->emptyValuesList[] = "{$title}'s email";
            }

            $this->readmeGenerator->removeTag($filePart, $key);
        }

        $this->readmeGenerator->setReadmeValue($filePart, 'team_lead_link', $this->codeOwnerEmail);

        $this->readmeGenerator->updateReadmeFile($filePart);
    }

    protected function fillGettingStarted(): void
    {
        $gitProjectPath = trim((string) shell_exec('git ls-remote --get-url origin'));
        $projectDirectory = basename($gitProjectPath, '.git');
        $filePart = $this->readmeGenerator->loadReadmePart('GETTING_STARTED.md');

        $this->readmeGenerator->setReadmeValue($filePart, 'git_project_path', $gitProjectPath);
        $this->readmeGenerator->setReadmeValue($filePart, 'project_directory', $projectDirectory);

        $this->readmeGenerator->updateReadmeFile($filePart);
    }

    protected function fillCredentialsAndAccess(string $kebabName): void
    {
        $filePart = $this->readmeGenerator->loadReadmePart('CREDENTIALS_AND_ACCESS.md');

        if (!empty($this->adminCredentials)) {
            $this->readmeGenerator->setReadmeValue($filePart, 'admin_email', $this->adminCredentials['email']);
            $this->readmeGenerator->setReadmeValue($filePart, 'admin_password', $this->adminCredentials['password']);
        }

        $this->readmeGenerator->removeTag($filePart, 'admin_credentials', !$this->adminCredentials);

        foreach (self::CREDENTIALS_ITEMS as $key => $title) {
            if (!Arr::get($this->resources, $key)) {
                $this->readmeGenerator->removeTag($filePart, "{$key}_credentials", true);

                continue;
            }

            if (!empty($this->adminCredentials) && $this->confirm("Is {$title}'s admin the same as default one?", true)) {
                $adminCredentials = $this->adminCredentials;
            } else {
                if ($this->authType === AuthTypeEnum::Clerk && !$this->isMigrationExists('admins_create_table')) {
                    $this->publishAdminsTableMigration();
                }

                $adminCredentials = $this->createAdminUser($kebabName, $key, $title);
            }

            $this->readmeGenerator->setReadmeValue($filePart, "{$key}_email", $adminCredentials['email']);
            $this->readmeGenerator->setReadmeValue($filePart, "{$key}_password", $adminCredentials['password']);
            $this->readmeGenerator->removeTag($filePart, "{$key}_credentials");
        }

        $this->readmeGenerator->updateReadmeFile($filePart);
    }

    protected function setupComposerHooks(): void
    {
        $path = base_path('composer.json');

        $content = file_get_contents($path);

        $data = json_decode($content, true);

        $this->addArrayItemIfMissing($data, 'extra.hooks.config.stop-on-failure', 'pre-commit');
        $this->addArrayItemIfMissing($data, 'extra.hooks.pre-commit', 'docker compose up -d php && docker compose exec -T nginx vendor/bin/pint --repair');
        $this->addArrayItemIfMissing($data, 'scripts.post-install-cmd', '[ $COMPOSER_DEV_MODE -eq 0 ] || cghooks add --ignore-lock');
        $this->addArrayItemIfMissing($data, 'scripts.post-update-cmd', 'cghooks update');

        $resultData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

        file_put_contents($path, $resultData);
    }

    protected function addArrayItemIfMissing(array &$data, string $path, string $value): void
    {
        $current = Arr::get($data, $path, []);

        if (!in_array($value, $current)) {
            $current[] = $value;

            Arr::set($data, $path, $current);
        }
    }

    protected function setAutoDocContactEmail(string $email): void
    {
        $config = ArrayFile::open(base_path('config/auto-doc.php'));

        $config->set('info.contact.email', $email);

        $config->write();
    }

    protected function runMigrations(): void
    {
        config([
            'database.default' => $this->dbConnection,
            'database.connections.pgsql' => [
                'driver' => $this->dbConnection,
                'host' => $this->dbHost,
                'port' => $this->dbPort,
                'database' => $this->dbName,
                'username' => $this->dbUserName,
                'password' => '',
            ],
        ]);

        shell_exec('php artisan migrate --ansi --force');
    }

    protected function createAdminUser(string $kebabName, string $serviceKey = '', string $serviceName = ''): array
    {
        $adminEmail = when(empty($serviceKey), "admin@{$kebabName}.com", "admin.{$serviceKey}@{$kebabName}.com");
        $defaultPassword = substr(md5(uniqid()), 0, 8);

        $serviceLabel = when(!empty($serviceName), " for {$serviceName}");

        $adminCredentials = [
            'email' => $this->ask("Please enter admin email{$serviceLabel}", $adminEmail),
            'password' => $this->ask("Please enter admin password{$serviceLabel}", $defaultPassword),
        ];

        if ($this->authType === AuthTypeEnum::None) {
            $adminCredentials['name'] = $this->ask("Please enter admin name{$serviceLabel}", "{$serviceName} Admin");
            $adminCredentials['role_id'] = $this->ask("Please enter admin role id{$serviceLabel}", RoleEnum::Admin->value);
        }

        if (empty($serviceName)) {
            $this->adminCredentials = $adminCredentials;
        }

        $this->publishAdminMigration($adminCredentials, $serviceKey);

        return $adminCredentials;
    }

    protected function publishClass(View $template, string $fileName, string $filePath): void
    {
        $fileName = "{$fileName}.php";

        if (!is_dir($filePath)) {
            mkdir($filePath, 0777, true);
        }

        $data = $template->render();

        file_put_contents("{$filePath}/{$fileName}", "<?php\n\n{$data}");
    }

    protected function publishMigration(View $view, string $migrationName): void
    {
        $time = Carbon::now()->format('Y_m_d_His');

        $migrationName = "{$time}_{$migrationName}";

        $this->publishClass($view, $migrationName, 'database/migrations');
    }

    protected function updateEnvFile(string $fileName, array $data): void
    {
        $env = EnvFile::open($fileName);

        $env->addEmptyLine();

        $env->set($data);

        $env->write();
    }

    protected function prepareAppName(): void
    {
        $this->appName = $this->argument('application-name');

        $pascalCaseAppName = ucfirst(Str::camel($this->appName));

        if ($this->appName !== $pascalCaseAppName && $this->confirm("The application name is not in PascalCase, would you like to use {$pascalCaseAppName}", true)) {
            $this->appName = $pascalCaseAppName;
        }
    }

    protected function saveRenovateJSON(): void
    {
        $reviewer = $this->validateInput(
            method: fn () => $this->ask('Please type username of the project reviewer', Str::before($this->codeOwnerEmail, '@')),
            field: 'username of the project reviewer',
            rules: 'required|alpha_dash',
        );

        $data = [
            '$schema' => 'https://docs.renovatebot.com/renovate-schema.json',
            'extends' => ['config:recommended'],
            'enabledManagers' => ['composer'],
            'assignees' => [$reviewer],
        ];

        file_put_contents('renovate.json', json_encode($data, JSON_PRETTY_PRINT));
    }

    protected function validateInput(callable $method, string $field, string|array $rules): string
    {
        $value = $method();

        $validator = Validator::make([$field => $value], [$field => $rules]);

        if ($validator->fails()) {
            $this->warn($validator->errors()->first());

            $value = $this->validateInput($method, $field, $rules);
        }

        return $value;
    }

    protected function enableClerk(): void
    {
        array_push(
            $this->shellCommands,
            'composer require ronasit/laravel-clerk',
            'php artisan laravel-clerk:install',
        );

        $this->publishMigration(
            view: view('initializator::users_add_clerk_id_field'),
            migrationName: 'users_add_clerk_id_field',
        );

        $this->publishClass(
            template: view('initializator::clerk_user_repository'),
            fileName: 'ClerkUserRepository',
            filePath: 'app/Support/Clerk',
        );
    }

    protected function publishWebLogin(): void
    {
        shell_exec('php artisan vendor:publish --tag=initializator-web-login --force');

        file_put_contents(base_path('routes/web.php'), "\nAuth::routes();\n", FILE_APPEND);
    }

    protected function changeMiddlewareForTelescopeAuthorization(): void
    {
        $config = ArrayFile::open(base_path('config/telescope.php'));

        // TODO: add Authorize::class middleware after inplementing an ability to modify functions in the https://github.com/RonasIT/larabuilder package
        $config->set('middleware', [
            'web',
            'auth:web',
        ]);

        $config->write();
    }

    protected function publishAdminMigration(array $adminCredentials, ?string $serviceKey): void
    {
        $migrationName = (empty($serviceKey)) ? 'add_default_admin' : "add_{$serviceKey}_admin";

        $viewName = ($this->authType === AuthTypeEnum::Clerk)
            ? 'initializator::admins_add_additional_admin'
            : 'initializator::add_default_user';

        $this->publishMigration(
            view: view($viewName)->with($adminCredentials),
            migrationName: $migrationName,
        );
    }

    protected function isMigrationExists(string $migrationName): bool
    {
        return !empty(glob(base_path("database/migrations/*_{$migrationName}.php")));
    }

    protected function publishAdminsTableMigration(): void
    {
        $this->publishMigration(
            view: view('initializator::admins_create_table'),
            migrationName: 'admins_create_table',
        );
    }
}
