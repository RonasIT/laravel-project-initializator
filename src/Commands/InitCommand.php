<?php

namespace RonasIT\ProjectInitializator\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use RonasIT\ProjectInitializator\Enums\AuthTypeEnum;
use RonasIT\ProjectInitializator\Enums\RoleEnum;
use RonasIT\ProjectInitializator\Enums\AppTypeEnum;
use Winter\LaravelConfigWriter\ArrayFile;

class InitCommand extends Command implements Isolatable
{
    public const string TEMPLATES_PATH = 'vendor/ronasit/laravel-project-initializator/resources/md/readme';

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

    protected string $readmeContent = '';

    protected array $shellCommands = [
        'composer require ronasit/laravel-helpers',
        'composer require ronasit/laravel-swagger',
        'php artisan vendor:publish --provider="RonasIT\\AutoDoc\\AutoDocServiceProvider"',
        'composer require --dev ronasit/laravel-entity-generator',
    ];

    protected string $appName;

    protected AppTypeEnum $appType;

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

        $envFile = (file_exists('.env')) ? '.env' : '.env.example';

        $this->createOrUpdateConfigFile($envFile, '=', [
            'APP_NAME' => $this->appName,
        ]);

        $this->createOrUpdateConfigFile('.env.development', '=', [
            'APP_NAME' => $this->appName,
            'APP_URL' => $this->appUrl,
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

            $this->createOrUpdateConfigFile('.env.development', '=', $data);
            $this->createOrUpdateConfigFile('.env.example', '=', $data);

            if ($envFile === '.env') {
                $this->createOrUpdateConfigFile($envFile, '=', $data);
            }
        }

        if ($this->confirm('Do you want to generate an admin user?', true)) {
            $this->createAdminUser($kebabName);
        }

        if ($shouldGenerateReadme = $this->confirm('Do you want to generate a README file?', true)) {
            $this->fillReadme();

            if ($this->confirm('Do you need a `Resources & Contacts` part?', true)) {
                $this->fillResourcesAndContacts();
                $this->fillResources();
                $this->fillContacts();
            }

            if ($this->confirm('Do you need a `Prerequisites` part?', true)) {
                $this->fillPrerequisites();
            }

            if ($this->confirm('Do you need a `Getting Started` part?', true)) {
                $this->fillGettingStarted();
            }

            if ($this->confirm('Do you need an `Environments` part?', true)) {
                $this->fillEnvironments();
            }

            if ($this->confirm('Do you need a `Credentials and Access` part?', true)) {
                $this->fillCredentialsAndAccess($kebabName);

                if ($this->authType === AuthTypeEnum::Clerk) {
                    $this->fillClerkAuthType();
                }
            }

            $this->saveReadme();

            $this->info('README generated successfully!');

            if ($this->emptyValuesList) {
                $this->warn('Don`t forget to fill the following empty values:');

                foreach ($this->emptyValuesList as $value) {
                    $this->warn("- {$value}");
                }
            }
        }

        if ($this->confirm('Would you use Renovate dependabot?', true)) {
            $this->saveRenovateJSON();

            if ($shouldGenerateReadme) {
                $this->fillRenovate();

                $this->saveReadme();
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
            $this->shellCommands[] = 'composer remove --dev ronasit/laravel-project-initializator';
        }

        foreach ($this->shellCommands as $shellCommand) {
            shell_exec("{$shellCommand} --ansi");
        }

        $this->setAutoDocContactEmail($this->codeOwnerEmail);

        Artisan::call('migrate');
    }

    protected function setAutoDocContactEmail(string $email): void
    {
        $config = ArrayFile::open(base_path('config/auto-doc.php'));
        
        $config->set('info.contact.email', $email);

        $config->write();
    }

    protected function createAdminUser(string $kebabName): void
    {
        $defaultPassword = substr(md5(uniqid()), 0, 8);

        $this->adminCredentials = [
            'email' => $this->ask('Please enter an admin email', "admin@{$kebabName}.com"),
            'password' => $this->ask('Please enter an admin password', $defaultPassword),
        ];

        if ($this->authType === AuthTypeEnum::Clerk) {
            $this->publishMigration(
                view: view('initializator::admins_create_table')->with($this->adminCredentials),
                migrationName: 'admins_create_table',
            );
        } else {
            $this->adminCredentials['name'] = $this->ask('Please enter an admin name', 'Admin');
            $this->adminCredentials['role_id'] = $this->ask('Please enter an admin role id', RoleEnum::Admin->value);

            $this->publishMigration(
                view: view('initializator::add_default_user')->with($this->adminCredentials),
                migrationName: 'add_default_user',
            );
        }
    }

    protected function fillReadme(): void
    {
        $file = $this->loadReadmePart('README.md');

        $this->setReadmeValue($file, 'project_name', $this->appName);

        $this->setReadmeValue($file, 'type', $this->appType->value);

        $this->readmeContent = $file;
    }

    protected function fillResourcesAndContacts(): void
    {
        $filePart = $this->loadReadmePart('RESOURCES_AND_CONTACTS.md');

        $this->updateReadmeFile($filePart);
    }

    protected function fillResources(): void
    {
        $filePart = $this->loadReadmePart('RESOURCES.md');
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
                $this->setReadmeValue($filePart, "{$key}_link");
                $this->setReadmeValue($filePart, "{$key}_later", $laterText);
            } elseif ($link !== 'no') {
                $this->setReadmeValue($filePart, "{$key}_link", $link);
                $this->setReadmeValue($filePart, "{$key}_later");
            }

            $this->resources[$key] = ($link !== 'no');

            $this->removeTag($filePart, $key, $link === 'no');
        }

        $this->setReadmeValue($filePart, 'api_link', $this->appUrl);
        $this->updateReadmeFile($filePart);
    }

    protected function fillContacts(): void
    {
        $filePart = $this->loadReadmePart('CONTACTS.md');

        foreach (self::CONTACTS_ITEMS as $key => $title) {
            if ($link = $this->ask("Please enter a {$title}'s email", '')) {
                $this->setReadmeValue($filePart, "{$key}_link", $link);
            } else {
                $this->emptyValuesList[] = "{$title}'s email";
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
        $gitProjectPath = trim((string) shell_exec('git ls-remote --get-url origin'));
        $projectDirectory = basename($gitProjectPath, '.git');
        $filePart = $this->loadReadmePart('GETTING_STARTED.md');

        $this->setReadmeValue($filePart, 'git_project_path', $gitProjectPath);
        $this->setReadmeValue($filePart, 'project_directory', $projectDirectory);

        $this->updateReadmeFile($filePart);
    }

    protected function fillEnvironments(): void
    {
        $filePart = $this->loadReadmePart('ENVIRONMENTS.md');

        $this->setReadmeValue($filePart, 'api_link', $this->appUrl);
        $this->updateReadmeFile($filePart);
    }

    protected function fillCredentialsAndAccess(string $kebabName): void
    {
        $filePart = $this->loadReadmePart('CREDENTIALS_AND_ACCESS.md');

        if (!empty($this->adminCredentials)) {
            $this->setReadmeValue($filePart, 'admin_email', $this->adminCredentials['email']);
            $this->setReadmeValue($filePart, 'admin_password', $this->adminCredentials['password']);
        }

        $this->removeTag($filePart, 'admin_credentials', !$this->adminCredentials);

        foreach (self::CREDENTIALS_ITEMS as $key => $title) {
            if (!Arr::get($this->resources, $key)) {
                $this->removeTag($filePart, "{$key}_credentials", true);

                continue;
            }

            if (!empty($this->adminCredentials) && $this->confirm("Is {$title}'s admin the same as default one?", true)) {
                $email = $this->adminCredentials['email'];
                $password = $this->adminCredentials['password'];
            } else {
                $defaultPassword = substr(md5(uniqid()), 0, 8);

                $email = $this->ask("Please enter a {$title}'s admin email", "admin@{$kebabName}.com");
                $password = $this->ask("Please enter a {$title}'s admin password", $defaultPassword);
            }

            $this->setReadmeValue($filePart, "{$key}_email", $email);
            $this->setReadmeValue($filePart, "{$key}_password", $password);
            $this->removeTag($filePart, "{$key}_credentials");
        }

        $this->updateReadmeFile($filePart);
    }

    protected function fillClerkAuthType(): void
    {
        $filePart = $this->loadReadmePart('CLERK.md');

        $this->updateReadmeFile($filePart);
    }

    protected function addQuotes($string): string
    {
        return (Str::contains($string, ' ')) ? "\"{$string}\"" : $string;
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

    protected function createOrUpdateConfigFile(string $fileName, string $separator, array $data): void
    {
        $parsed = file_get_contents($fileName);

        $lines = explode("\n", $parsed);

        $previousKey = null;

        foreach ($data as $key => $value) {
            $value = $this->addQuotes($value);

            foreach ($lines as &$line) {
                if (Str::contains($line, $key)) {
                    $line = "{$key}{$separator}{$value}";

                    continue 2;
                }
            }

            $item = "{$key}{$separator}{$value}";

            if (!empty($previousKey) && $this->configKeysHaveSamePrefix($key, $previousKey)) {
                $lines[] = $item;
            } else {
                $lines[] = "\n{$item}";
            }

            $previousKey = $key;
        }

        $ymlSettings = implode("\n", $lines);

        file_put_contents($fileName, $ymlSettings);
    }

    protected function configKeysHaveSamePrefix(string $key, string $previousKey): bool
    {
        return Str::before($key, '_') === Str::before($previousKey, '_');
    }

    protected function loadReadmePart(string $fileName): string
    {
        $file = base_path(DIRECTORY_SEPARATOR . self::TEMPLATES_PATH . DIRECTORY_SEPARATOR . $fileName);

        return file_get_contents($file);
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

    protected function setReadmeValue(string &$file, string $key, string $value = ''): void
    {
        $file = str_replace(":{$key}", $value, $file);
    }

    protected function saveReadme(): void
    {
        file_put_contents('README.md', $this->readmeContent);
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

    protected function fillRenovate(): void
    {
        $filePart = $this->loadReadmePart('RENOVATE.md');

        $this->updateReadmeFile($filePart);
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
}
