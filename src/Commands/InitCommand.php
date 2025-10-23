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
use RonasIT\ProjectInitializator\Extensions\ConfigWriter\ArrayFile;
use RonasIT\ProjectInitializator\Generators\ReadmeGenerator;
use Winter\LaravelConfigWriter\EnvFile;

class InitCommand extends Command implements Isolatable
{
    protected $signature = 'init {application-name : The application name }';
    protected $description = 'Initialize required project parameters to run DEV environment';

    protected array $adminCredentials = [];

    protected array $readmeParts = [];

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
    protected string $kebabName;
    protected string $appUrl;
    protected AppTypeEnum $appType;
    protected AuthTypeEnum $authType;
    protected string $codeOwnerEmail;

    protected string $envFile;
    protected array $envConfig = [
        'dbConnection' => 'pgsql',
        'dbHost' => 'pgsql',
        'dbPort' => '5432',
        'dbName' => 'postgres',
        'dbUserName' => 'postgres',
    ];

    public function __construct(
        protected ReadmeGenerator $readmeGenerator,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->prepareAppName();

        $this->codeOwnerEmail = $this->validateInput(
            method: fn () => $this->ask('Please specify a Code Owner/Team Lead\'s email'),
            field: 'email of code owner / team lead',
            rules: 'required|email',
        );

        $this->appUrl = $this->ask('Please enter an application URL', "https://api.dev.{$this->kebabName}.com");

        $this->updateEnvFile();

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

        $this->configureClerk();

        if ($this->confirm('Do you want to generate an admin user?', true)) {
            if ($this->authType === AuthTypeEnum::Clerk) {
                $this->publishAdminsTableMigration();
            }

            $this->createAdminUser();
        }

        if ($shouldGenerateReadme = $this->confirm('Do you want to generate a README file?', true)) {
            $this->configureReadmeParts();
        }

        if ($this->confirm('Would you use Renovate dependabot?', true)) {
            $this->saveRenovateJSON();

            $this->readmeParts[] = 'fillRenovate';
        }

        if ($shouldGenerateReadme) {
            $this->readmeGenerator->generate($this->readmeParts);

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

        $this->changeMiddlewareForTelescopeAuthorization();

        $this->setAutoDocContactEmail($this->codeOwnerEmail);

        foreach ($this->shellCommands as $shellCommand) {
            shell_exec("{$shellCommand} --ansi");
        }

        $this->publishWebLogin();

        if ($this->shouldUninstallPackage) {
            shell_exec('composer remove --dev ronasit/laravel-project-initializator --ansi');
        }

        $this->runMigrations();
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

    protected function prepareAppName(): void
    {
        $this->appName = $this->argument('application-name');

        $pascalCaseAppName = ucfirst(Str::camel($this->appName));

        if ($this->appName !== $pascalCaseAppName && $this->confirm("The application name is not in PascalCase, would you like to use {$pascalCaseAppName}", true)) {
            $this->appName = $pascalCaseAppName;
        }

        $this->kebabName = Str::kebab($this->appName);
    }

    protected function updateEnvFile(): void
    {
        $this->envFile = (file_exists('.env')) ? '.env' : '.env.example';

        $envConfig = [
            'APP_NAME' => $this->appName,
            'DB_CONNECTION' => $this->envConfig['dbConnection'],
            'DB_HOST' => $this->envConfig['dbHost'],
            'DB_PORT' => $this->envConfig['dbPort'],
            'DB_DATABASE' => $this->envConfig['dbName'],
            'DB_USERNAME' => $this->envConfig['dbUserName'],
            'DB_PASSWORD' => '',
        ];

        $this->writeEnvFile($this->envFile, $envConfig);

        if (!file_exists('.env.development')) {
            copy('.env.example', '.env.development');
        }

        $this->writeEnvFile('.env.development', [
            'APP_NAME' => $this->appName,
            'APP_URL' => $this->appUrl,
            'APP_MAINTENANCE_DRIVER' => 'cache',
            'CACHE_STORE' => 'redis',
            'QUEUE_CONNECTION' => 'redis',
            'SESSION_DRIVER' => 'redis',
            'DB_CONNECTION' => $this->envConfig['dbConnection'],
            'DB_HOST' => '',
            'DB_PORT' => '',
            'DB_DATABASE' => '',
            'DB_USERNAME' => '',
            'DB_PASSWORD' => '',
        ]);
    }

    protected function configureClerk(): void
    {
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

            $this->writeEnvFile('.env.development', $data);
            $this->writeEnvFile($this->envFile, $data);

            if ($this->envFile !== '.env.example') {
                $this->writeEnvFile('.env.example', $data);
            }
        }
    }

    protected function writeEnvFile(string $fileName, array $data): void
    {
        $env = EnvFile::open($fileName);

        // TODO: After updating wintercms/laravel-config-writer, remove the key comparison check and keep only $env->addEmptyLine();
        $envKeys = array_column($env->getAst(), 'match');
        $dataKeys = array_keys($data);

        $hasMissingKeys = count(array_intersect($dataKeys, $envKeys)) !== count($dataKeys);

        if ($hasMissingKeys) {
            $env->addEmptyLine();
        }

        $env->set($data);

        $env->write();
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

    protected function createAdminUser(string $serviceKey = '', string $serviceName = ''): array
    {
        $adminEmail = when(empty($serviceKey), "admin@{$this->kebabName}.com", "admin.{$serviceKey}@{$this->kebabName}.com");
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

    protected function publishMigration(View $view, string $migrationName): void
    {
        $time = Carbon::now()->format('Y_m_d_His');

        $migrationName = "{$time}_{$migrationName}";

        $this->publishClass($view, $migrationName, 'database/migrations');
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

    protected function configureReadmeParts(): void
    {
        $this->readmeGenerator->appInfo = [
            'name' => $this->appName,
            'type' => $this->appType->value,
            'url' => $this->appUrl,
            'code_owner_email' => $this->codeOwnerEmail,
        ];

        if ($this->confirm('Do you need a `Resources & Contacts` part?', true)) {
            $this->configureResources();
            $this->configureContacts();

            $this->readmeParts[] = 'fillResourcesAndContacts';
            $this->readmeParts[] = 'fillResources';
            $this->readmeParts[] = 'fillContacts';
        }

        if ($this->confirm('Do you need a `Prerequisites` part?', true)) {
            $this->readmeParts[] = 'fillPrerequisites';
        }

        if ($this->confirm('Do you need a `Getting Started` part?', true)) {
            $this->readmeParts[] = 'fillGettingStarted';
        }

        if ($this->confirm('Do you need an `Environments` part?', true)) {
            $this->readmeParts[] = 'fillEnvironments';
        }

        if ($this->confirm('Do you need a `Credentials and Access` part?', true)) {
            $this->configureCredentialsAndAccess();

            $this->readmeParts[] = 'fillCredentialsAndAccess';

            if ($this->authType === AuthTypeEnum::Clerk) {
                $this->readmeParts[] = 'fillClerkAuthType';
            }
        }
    }

    protected function configureResources(): void
    {
        foreach ($this->readmeGenerator->resourcesItems as $key => $resource) {
            $defaultAnswer = (Arr::has($resource, 'default_url')) ? $this->appUrl . "/{$key}" : 'later';
            $text = "Are you going to use {$resource['title']}? "
                . 'Please enter a link or select `later` to do it later, otherwise select `no`.';

            $link = $this->anticipate($text, ['later', 'no'], $defaultAnswer);

            if ($link === 'later') {
                $this->emptyValuesList[] = "{$resource['title']} link";
            }

            $this->readmeGenerator->resourcesItems[$key]['link'] = $link;
            $this->readmeGenerator->resourcesItems[$key]['active'] = ($link !== 'no');
        }
    }

    protected function configureContacts(): void
    {
        foreach ($this->readmeGenerator->contactsItems as $key => $value) {
            if ($link = $this->ask("Please enter a {$value['title']}'s email", '')) {
                $this->readmeGenerator->contactsItems[$key]['email'] = $link;
            } else {
                $this->emptyValuesList[] = "{$value['title']}'s email";
            }
        }
    }

    protected function configureCredentialsAndAccess(): void
    {
        foreach ($this->readmeGenerator->credentialsItems as $key => &$item) {
            if (!Arr::get($this->readmeGenerator->resourcesItems, "{$key}.active")) {
                continue;
            }

            if (!empty($this->adminCredentials) && $this->confirm("Is {$item['title']}'s admin the same as default one?", true)) {
                $adminCredentials = $this->adminCredentials;
            } else {
                if ($this->authType === AuthTypeEnum::Clerk && !$this->isMigrationExists('admins_create_table')) {
                    $this->publishAdminsTableMigration();
                }

                $adminCredentials = $this->createAdminUser($key, $item['title']);
            }

            $item['email'] = $adminCredentials['email'];
            $item['password'] = $adminCredentials['password'];
        }

        if (!empty($this->adminCredentials)) {
            $this->readmeGenerator->credentialsItems['admin'] = [
                'title' => 'Default admin',
                'email' => $this->adminCredentials['email'],
                'password' => $this->adminCredentials['password'],
            ];
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

    protected function setAutoDocContactEmail(string $email): void
    {
        $config = ArrayFile::open(base_path('config/auto-doc.php'));

        $config->set('info.contact.email', $email);

        $config->write();
    }

    protected function publishWebLogin(): void
    {
        shell_exec('php artisan vendor:publish --tag=initializator-web-login --force');

        file_put_contents(base_path('routes/web.php'), "\nAuth::routes();\n", FILE_APPEND);
    }

    protected function runMigrations(): void
    {
        config([
            'database.default' => $this->envConfig['dbConnection'],
            'database.connections.pgsql' => [
                'driver' => $this->envConfig['dbConnection'],
                'host' => $this->envConfig['dbHost'],
                'port' => $this->envConfig['dbPort'],
                'database' => $this->envConfig['dbName'],
                'username' => $this->envConfig['dbUserName'],
                'password' => '',
            ],
        ]);

        shell_exec('php artisan migrate --ansi');
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
