<?php

namespace RonasIT\ProjectInitializator\Generators;

use Illuminate\Support\Arr;
use RonasIT\ProjectInitializator\Enums\AppTypeEnum;
use RonasIT\ProjectInitializator\Enums\StorageEnum;
use Winter\LaravelConfigWriter\EnvFile;

class EnvGenerator
{
    public const array DEFAULT_DB_CONNECTION_CONFIG = [
        'driver' => 'pgsql',
        'host' => 'pgsql',
        'port' => '5432',
        'database' => 'postgres',
        'username' => 'postgres',
        'password' => '',
    ];

    protected string $appName;
    protected string $appUrl;

    public function setAppInfo(string $appName, string $appUrl): self
    {
        $this->appName = $appName;
        $this->appUrl = $appUrl;

        return $this;
    }

    public function generate(): void
    {
        $this->writeBaseEnvFiles();
    }

    public function configureClerk(AppTypeEnum $appType): void
    {
        $data = [
            'AUTH_GUARD' => 'clerk',
            'CLERK_ALLOWED_ISSUER' => '',
            'CLERK_SECRET_KEY' => '',
            'CLERK_SIGNER_KEY_PATH' => '',
        ];

        if ($appType !== AppTypeEnum::Mobile) {
            $data['CLERK_ALLOWED_ORIGINS'] = '';
        }

        $this->updateEnvFile('.env', $data);
        $this->updateEnvFile('.env.example', $data);
        $this->updateEnvFile('.env.development', Arr::except($data, ['CLERK_SIGNER_KEY_PATH']));
    }

    public function configureGcsStorage(): void
    {
        $this->updateEnvFile('.env.development', [
            'FILESYSTEM_DISK' => StorageEnum::GCS->value,
            'GOOGLE_CLOUD_STORAGE_PATH_PREFIX' => 'api',
            'GOOGLE_CLOUD_STORAGE_BUCKET' => '',
            'GOOGLE_CLOUD_PROJECT_ID' => '',
        ]);
    }

    protected function writeBaseEnvFiles(): void
    {
        $envConfig = [
            'APP_NAME' => $this->appName,
            'DB_CONNECTION' => self::DEFAULT_DB_CONNECTION_CONFIG['driver'],
            'DB_HOST' => self::DEFAULT_DB_CONNECTION_CONFIG['host'],
            'DB_PORT' => self::DEFAULT_DB_CONNECTION_CONFIG['port'],
            'DB_DATABASE' => self::DEFAULT_DB_CONNECTION_CONFIG['database'],
            'DB_USERNAME' => self::DEFAULT_DB_CONNECTION_CONFIG['username'],
            'DB_PASSWORD' => self::DEFAULT_DB_CONNECTION_CONFIG['password'],
        ];

        $this->updateEnvFile('.env.example', $envConfig);

        if (!file_exists('.env')) {
            copy('.env.example', '.env');
        } else {
            $this->updateEnvFile('.env', $envConfig);
        }

        $this->createEnvFileIfNotExists('.env.development', '.env.example');
        $this->updateEnvFile('.env.development', [
            'APP_NAME' => $this->appName,
            'APP_ENV' => 'development',
            'APP_URL' => $this->appUrl,
            'APP_MAINTENANCE_DRIVER' => 'cache',
            'APP_MAINTENANCE_STORE' => 'redis',
            'CACHE_STORE' => 'redis',
            'QUEUE_CONNECTION' => 'redis',
            'SESSION_DRIVER' => 'redis',
            'DB_CONNECTION' => self::DEFAULT_DB_CONNECTION_CONFIG['driver'],
            'DB_HOST' => '',
            'DB_PORT' => '',
            'DB_DATABASE' => '',
            'DB_USERNAME' => '',
            'DB_PASSWORD' => '',
        ]);

        $appKey = $this->generateAppKey();

        $testingEnvConfig = [
            ...$envConfig,
            'APP_ENV' => 'testing',
            'APP_KEY' => $appKey,
            'LOG_CHANNEL' => 'stderr',
        ];

        $this->createEnvFileIfNotExists('.env.ci-testing', '.env.example');
        $this->updateEnvFile('.env.ci-testing', [
            ...$testingEnvConfig,
            'DB_DATABASE' => 'forge',
            'DB_USERNAME' => 'forge',
        ]);

        $this->createEnvFileIfNotExists('.env.testing', '.env.example');
        $this->updateEnvFile('.env.testing', [
            ...$testingEnvConfig,
            'DB_HOST' => 'pgsql_test',
            'FAIL_EXPORT_JSON' => false,
        ]);
    }

    protected function createEnvFileIfNotExists(string $filePath, string $source): void
    {
        if (!file_exists($filePath)) {
            copy($source, $filePath);
        }
    }

    protected function updateEnvFile(string $fileName, array $data): void
    {
        $env = EnvFile::open($fileName);

        $env->addEmptyLine();

        $env->set($data);

        $env->write();
    }

    protected function generateAppKey(): string
    {
        return 'base64:' . base64_encode(random_bytes(32));
    }
}
