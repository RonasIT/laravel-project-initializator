<?php

namespace RonasIT\ProjectInitializator\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use RonasIT\Larabuilder\Builders\AppBootstrapBuilder;
use RonasIT\Larabuilder\Builders\PHPFileBuilder;
use RonasIT\ProjectInitializator\DTO\ResourceDTO;
use RonasIT\ProjectInitializator\Enums\AppTypeEnum;
use RonasIT\ProjectInitializator\Enums\AuthTypeEnum;
use RonasIT\ProjectInitializator\Enums\ReadmeBlockEnum;
use RonasIT\ProjectInitializator\Enums\RoleEnum;
use RonasIT\ProjectInitializator\Enums\StorageEnum;
use RonasIT\ProjectInitializator\Enums\UserAnswerEnum;
use RonasIT\ProjectInitializator\Generators\ReadmeGenerator;
use Winter\LaravelConfigWriter\ArrayFile;
use Winter\LaravelConfigWriter\EnvFile;

class InitCommand extends Command implements Isolatable
{
    protected $signature = 'init {application-name : The application name }';

    protected $description = 'Initialize required project parameters to run DEV environment';

    protected array $adminCredentials = [];

    protected array $emptyResourcesList = [];

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
        'php artisan key:generate',
    ];

    protected bool $shouldUninstallPackage = false;

    protected string $appName;
    protected string $kebabAppName;
    protected string $appUrl;
    protected AppTypeEnum $appType;
    protected AuthTypeEnum $authType;
    protected string $codeOwnerEmail;

    protected ?ReadmeGenerator $readmeGenerator = null;

    protected array $defaultDBConnectionConfig = [
        'driver' => 'pgsql',
        'host' => 'pgsql',
        'port' => '5432',
        'database' => 'postgres',
        'username' => 'postgres',
    ];

    protected ?Carbon $lastMigrationTimestamp = null;

    public function __construct()
    {
        $this->lastMigrationTimestamp = Carbon::now();

        parent::__construct();
    }

    public function handle(): void
    {
        $this->prepareAppName();

        $this->codeOwnerEmail = $this->askWithValidation(
            parameter: 'email of code owner / team lead',
            rules: 'required|email',
        );

        $this->appUrl = $this->ask('Please enter an application URL', "https://api.dev.{$this->kebabAppName}.com");

        $this->setupEnvFiles();

        $this->info('Project initialized successfully!');

        $this->appType = AppTypeEnum::from($this->choice(
            question: 'What type of application will your API serve?',
            choices: AppTypeEnum::values(),
            default: AppTypeEnum::Multiplatform->value,
        ));

        $this->authType = AuthTypeEnum::from($this->choice(
            question: 'Please choose the authentication type',
            choices: AuthTypeEnum::values(),
            default: AuthTypeEnum::None->value,
        ));

        if ($this->authType === AuthTypeEnum::Clerk) {
            $this->configureClerk();
        } else {
            $this->publishRoleMigrations();
        }

        if ($this->confirm('Do you want to generate an admin user?', true)) {
            if ($this->authType === AuthTypeEnum::Clerk) {
                $this->publishAdminsTableMigration();
            }

            $this->createAdminUser();
        }

        if ($this->confirm('Do you want to generate a README file?', true)) {
            $this->configureReadme();
        }

        if ($this->confirm('Will project work with media files? (upload, store and return content)')) {
            $this->setupMediaStorage();
        }

        if ($this->confirm('Would you use Renovate dependabot?', true)) {
            $this->saveRenovateJSON();

            $this->readmeGenerator?->addBlock(ReadmeBlockEnum::Renovate);
        }

        if (!empty($this->readmeGenerator)) {
            $this->readmeGenerator->save();

            $this->info('README generated successfully!');
        }

        if ($this->emptyResourcesList) {
            $this->warn('Don`t forget to fill the following empty values:');

            foreach ($this->emptyResourcesList as $value) {
                $this->warn("- {$value}");
            }
        }

        if (!class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            array_push(
                $this->shellCommands,
                'composer require ronasit/laravel-telescope-extension',
                'php artisan telescope:install',
            );
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

        $this->addDefaultHttpExceptionRender();

        $this->runMigrations();
    }

    protected function askWithValidation(string $parameter, string|array $rules, ?string $default = null): string
    {
        $question = "Please specify: {$parameter}";

        $value = $this->ask($question, $default);

        $validator = Validator::make([$parameter => $value], [$parameter => $rules]);

        if ($validator->fails()) {
            $this->warn($validator->errors()->first());

            $value = $this->askWithValidation($parameter, $rules, $default);
        }

        return $value;
    }

    protected function prepareAppName(): void
    {
        $appName = $this->argument('application-name');

        $pascalCaseAppName = ucfirst(Str::camel($appName));

        if ($appName !== $pascalCaseAppName && $this->confirm("The application name is not in PascalCase, would you like to use {$pascalCaseAppName}", true)) {
            $appName = $pascalCaseAppName;
        }

        $this->appName = $appName;
        $this->kebabAppName = Str::kebab($appName);
    }

    protected function setupEnvFiles(): void
    {
        $envConfig = [
            'APP_NAME' => $this->appName,
            'DB_CONNECTION' => $this->defaultDBConnectionConfig['driver'],
            'DB_HOST' => $this->defaultDBConnectionConfig['host'],
            'DB_PORT' => $this->defaultDBConnectionConfig['port'],
            'DB_DATABASE' => $this->defaultDBConnectionConfig['database'],
            'DB_USERNAME' => $this->defaultDBConnectionConfig['username'],
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
            'APP_ENV' => 'development',
            'APP_URL' => $this->appUrl,
            'APP_MAINTENANCE_DRIVER' => 'cache',
            'APP_MAINTENANCE_STORE' => 'redis',
            'CACHE_STORE' => 'redis',
            'QUEUE_CONNECTION' => 'redis',
            'SESSION_DRIVER' => 'redis',
            'DB_CONNECTION' => $this->defaultDBConnectionConfig['driver'],
            'DB_HOST' => '',
            'DB_PORT' => '',
            'DB_DATABASE' => '',
            'DB_USERNAME' => '',
            'DB_PASSWORD' => '',
        ]);
    }

    protected function configureClerk(): void
    {
        $this->enableClerk();

        new PHPFileBuilder(app_path('Models/User.php'))
            ->addArrayPropertyItem('fillable', 'clerk_id')
            ->save();

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

    protected function updateEnvFile(string $fileName, array $data): void
    {
        $env = EnvFile::open($fileName);

        $env->addEmptyLine();

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
            view: view('initializator::users_format_to_clerk'),
            migrationName: 'users_format_to_clerk',
        );

        $this->publishClass(
            template: view('initializator::clerk_user_repository'),
            fileName: 'ClerkUserRepository',
            filePath: 'app/Support/Clerk',
        );
    }

    protected function createAdminUser(string $serviceKey = '', string $serviceName = ''): array
    {
        $isServiceAdmin = (!empty($serviceKey) && !empty($serviceName));

        $adminEmail = when($isServiceAdmin, "admin.{$serviceKey}@{$this->kebabAppName}.com", "admin@{$this->kebabAppName}.com");
        $defaultPassword = substr(md5(uniqid()), 0, 8);

        $serviceLabel = when($isServiceAdmin, " for {$serviceName}");

        $adminCredentials = [
            'email' => $this->ask("Please enter admin email{$serviceLabel}", $adminEmail),
            'password' => $this->ask("Please enter admin password{$serviceLabel}", $defaultPassword),
        ];

        $adminName = when($isServiceAdmin, "{$serviceName} Admin", 'Admin');

        if ($this->authType === AuthTypeEnum::None) {
            $adminCredentials['name'] = $this->ask("Please enter admin name{$serviceLabel}", $adminName);
            $adminCredentials['role_id'] = $this->ask("Please enter admin role id{$serviceLabel}", RoleEnum::Admin->value);
        }

        if (!$isServiceAdmin) {
            $this->adminCredentials = $adminCredentials;
        }

        $this->publishAdminMigration($adminCredentials, $serviceKey);

        return $adminCredentials;
    }

    protected function publishRoleMigrations(): void
    {
        if (!$this->isMigrationExists('roles_create_table') && !$this->isMigrationExists('create_roles_table')) {
            $this->publishMigration(
                view: view('initializator::roles_create_table'),
                migrationName: 'roles_create_table',
            );

            $this->publishMigration(
                view: view('initializator::users_add_role_id'),
                migrationName: 'users_add_role_id',
            );
        }
    }

    protected function publishMigration(View $view, string $migrationName): void
    {
        $time = $this->lastMigrationTimestamp->addSecond();

        $migrationName = "{$time->format('Y_m_d_His')}_{$migrationName}";

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

    protected function configureReadme(): void
    {
        $this->readmeGenerator = app(ReadmeGenerator::class)->setAppInfo(
            appName: $this->appName,
            appType: $this->appType->value,
            appUrl: $this->appUrl,
            codeOwnerEmail: $this->codeOwnerEmail,
        );

        if ($this->confirm('Do you need a `Resources & Contacts` part?', true)) {
            $this->configureResources();
            $this->configureManagerEmail();
        }

        if ($this->confirm('Do you need a `Prerequisites` part?', true)) {
            $this->readmeGenerator?->addBlock(ReadmeBlockEnum::Prerequisites);
        }

        if ($this->confirm('Do you need a `Getting Started` part?', true)) {
            $gitProjectPath = trim((string) shell_exec('git ls-remote --get-url origin'));

            $this->readmeGenerator?->setGitProjectPath($gitProjectPath);

            $this->readmeGenerator?->addBlock(ReadmeBlockEnum::GettingStarted);
        }

        if ($this->confirm('Do you need an `Environments` part?', true)) {
            $this->readmeGenerator?->addBlock(ReadmeBlockEnum::Environments);
        }

        if ($this->confirm('Do you need a `Credentials and Access` part?', true)) {
            $this->configureCredentialsAndAccess();

            $this->readmeGenerator?->addBlock(ReadmeBlockEnum::CredentialsAndAccess);

            if ($this->authType === AuthTypeEnum::Clerk) {
                $this->readmeGenerator?->addBlock(ReadmeBlockEnum::Clerk);
            }
        }
    }

    protected function configureResources(): void
    {
        foreach ($this->readmeGenerator->getConfigurableResources() as $resource) {
            $defaultAnswer = ($resource->localPath) ? "{$this->appUrl}/{$resource->localPath}" : UserAnswerEnum::Later->value;

            $string = 'Are you going to use %? Please enter a link or select `%` to do it later, otherwise select `%`.';

            $text = Str::replaceArray('%', [
                $resource->title,
                UserAnswerEnum::Later->value,
                UserAnswerEnum::No->value,
            ], $string);

            $link = $this->anticipate($text, UserAnswerEnum::values(), $defaultAnswer);

            $answer = UserAnswerEnum::tryFrom($link);

            if (empty($answer)) {
                $resource->setLink($link);
            } elseif ($answer === UserAnswerEnum::Later) {
                $this->emptyResourcesList[] = "{$resource->title} link";
            }

            $resource->setActive($answer !== UserAnswerEnum::No);

            $this->readmeGenerator?->addResource($resource);
        }
    }

    protected function configureManagerEmail(): void
    {
        if ($link = $this->ask("Please enter a Manager's email", '')) {
            $this->readmeGenerator?->setManagerEmail($link);
        } else {
            $this->emptyResourcesList[] = "Manager's email";
        }
    }

    protected function configureCredentialsAndAccess(): void
    {
        foreach ($this->readmeGenerator->getAccessRequiredResources() as $resource) {
            if (!empty($this->adminCredentials) && $this->confirm("Is {$resource->title}'s admin the same as default one?", true)) {
                $adminCredentials = $this->adminCredentials;
            } else {
                if ($this->authType === AuthTypeEnum::Clerk && !$this->isMigrationExists('admins_create_table')) {
                    $this->publishAdminsTableMigration();
                }

                $adminCredentials = $this->createAdminUser($resource->key, $resource->title);
            }

            $resource->setCredentials($adminCredentials['email'], $adminCredentials['password']);
        }

        if (!empty($this->adminCredentials)) {
            $this->readmeGenerator?->addResource(new ResourceDTO(
                key: 'admin',
                title: 'Default admin',
                email: $this->adminCredentials['email'],
                password: $this->adminCredentials['password'],
            ));
        }
    }

    protected function setupMediaStorage(): void
    {
        $this->shellCommands[] = 'composer require ronasit/laravel-media';

        $storage = StorageEnum::from($this->choice(
            question: 'Which storage will be used for media files?',
            choices: StorageEnum::values(),
            default: StorageEnum::GCS->value,
        ));

        if ($storage === StorageEnum::GCS) {
            $this->shellCommands[] = 'composer require spatie/laravel-google-cloud-storage';

            $this->updateEnvFile('.env.development', [
                'FILESYSTEM_DISK' => StorageEnum::GCS->value,
                'GOOGLE_CLOUD_STORAGE_PATH_PREFIX' => 'api',
                'GOOGLE_CLOUD_STORAGE_BUCKET' => '',
                'GOOGLE_CLOUD_PROJECT_ID' => '',
            ]);

            $this->emptyResourcesList[] = 'GOOGLE_CLOUD_STORAGE_BUCKET';
            $this->emptyResourcesList[] = 'GOOGLE_CLOUD_PROJECT_ID';

            $this->addGcsDiskToConfig();
        }
    }

    protected function addGcsDiskToConfig(): void
    {
        $config = ArrayFile::open(base_path('config/filesystems.php'));

        $config
            ->set('disks.gcs.driver', StorageEnum::GCS->value)
            ->set('disks.gcs.key_file_path', null)
            ->set('disks.gcs.key_file', [])
            ->set('disks.gcs.project_id', $config->function('env', ['GOOGLE_CLOUD_PROJECT_ID', 'your-project-id']))
            ->set('disks.gcs.bucket', $config->function('env', ['GOOGLE_CLOUD_STORAGE_BUCKET', 'your-bucket']))
            ->set('disks.gcs.path_prefix', $config->function('env', ['GOOGLE_CLOUD_STORAGE_PATH_PREFIX', '']))
            ->set('disks.gcs.storage_api_uri', $config->function('env', ['GOOGLE_CLOUD_STORAGE_API_URI', null]))
            ->set('disks.gcs.api_endpoint', $config->function('env', ['GOOGLE_CLOUD_STORAGE_API_ENDPOINT', null]))
            ->set('disks.gcs.visibility', 'public')
            ->set('disks.gcs.visibility_handler', null)
            ->set('disks.gcs.throw', true)
            ->set('disks.gcs.metadata', ['cacheControl' => 'public,max-age=86400']);

        $config->write();
    }

    protected function saveRenovateJSON(): void
    {
        $reviewer = $this->askWithValidation(
            parameter: 'username of the project reviewer',
            rules: 'required|alpha_dash',
            default: Str::before($this->codeOwnerEmail, '@'),
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

        // TODO: add Authorize::class middleware after implementing an ability to modify functions in the https://github.com/RonasIT/larabuilder package
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

    protected function addDefaultHttpExceptionRender(): void
    {
        new AppBootstrapBuilder()
            ->addExceptionsRender(
                exceptionClass: 'Symfony\Component\HttpKernel\Exception\HttpException',
                renderBody: '
                    return ($request->expectsJson())
                        ? response()->json([\'error\' => $exception->getMessage()], $exception->getStatusCode())
                        : null;
                ',
                includeRequestArg: true,
            )
            ->save();
    }

    protected function runMigrations(): void
    {
        config([
            'database.default' => $this->defaultDBConnectionConfig['driver'],
            "database.connections.{$this->defaultDBConnectionConfig['driver']}" => [
                'password' => '',
                ...$this->defaultDBConnectionConfig,
            ],
        ]);

        shell_exec('php artisan migrate --ansi --force');
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
