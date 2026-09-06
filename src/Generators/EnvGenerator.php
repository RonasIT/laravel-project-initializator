<?php

namespace RonasIT\ProjectInitializator\Generators;

use RonasIT\ProjectInitializator\Enums\AppTypeEnum;
use RonasIT\ProjectInitializator\Enums\EnvFileEnum;
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

    protected array $envVariables = [];

    public function setupEnv(string $appName, string $appUrl): void
    {
        $this->setEnvVariables([
            'APP_NAME' => $appName,
            'DB_CONNECTION' => self::DEFAULT_DB_CONNECTION_CONFIG['driver'],
            'DB_HOST' => self::DEFAULT_DB_CONNECTION_CONFIG['host'],
            'DB_PORT' => self::DEFAULT_DB_CONNECTION_CONFIG['port'],
            'DB_DATABASE' => self::DEFAULT_DB_CONNECTION_CONFIG['database'],
            'DB_USERNAME' => self::DEFAULT_DB_CONNECTION_CONFIG['username'],
            'DB_PASSWORD' => self::DEFAULT_DB_CONNECTION_CONFIG['password'],
        ], ...EnvFileEnum::cases());

        $this->configureDevelopment($appUrl);

        $this->configureTesting();
    }

    public function configureClerk(AppTypeEnum $appType): void
    {
        $data = [
            'AUTH_GUARD' => 'clerk',
            'CLERK_ALLOWED_ISSUER' => '',
            'CLERK_SECRET_KEY' => '',
        ];

        if ($appType !== AppTypeEnum::Mobile) {
            $data['CLERK_ALLOWED_ORIGINS'] = '';
        }

        $this->setEnvVariables($data, EnvFileEnum::Local, EnvFileEnum::Example, EnvFileEnum::Development);

        $this->setEnvVariables([
            'CLERK_SIGNER_KEY_PATH' => '',
        ], EnvFileEnum::Local, EnvFileEnum::Example);
    }

    public function setFilesystemDisk(StorageEnum $storage): void
    {
        $this->setEnvVariables([
            'FILESYSTEM_DISK' => $storage->value,
        ], EnvFileEnum::Development);
    }

    public function configureGcsStorage(): void
    {
        $this->setEnvVariables([
            'GOOGLE_CLOUD_STORAGE_PATH_PREFIX' => 'api',
            'GOOGLE_CLOUD_STORAGE_BUCKET' => '',
            'GOOGLE_CLOUD_PROJECT_ID' => '',
        ], EnvFileEnum::Development);
    }

    public function apply(): void
    {
        $this->createMissingEnvFiles();

        foreach (EnvFileEnum::cases() as $envFile) {
            $this->updateEnvFile($envFile->value, $this->envVariables[$envFile->value]);
        }
    }

    protected function createMissingEnvFiles(): void
    {
        foreach (EnvFileEnum::cases() as $envFile) {
            if ($envFile !== EnvFileEnum::Example) {
                $this->createEnvFileIfNotExists($envFile->value, EnvFileEnum::Example->value);
            }
        }
    }

    protected function configureDevelopment(string $appUrl): void
    {
        $this->setEnvVariables([
            'APP_ENV' => 'development',
            'APP_URL' => $appUrl,
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
        ], EnvFileEnum::Development);
    }

    protected function configureTesting(): void
    {
        $appKey = $this->generateAppKey();

        $this->setEnvVariables([
            'APP_ENV' => 'testing',
            'APP_KEY' => $appKey,
            'LOG_CHANNEL' => 'stderr',
            'DB_HOST' => 'pgsql_test',
        ], EnvFileEnum::CiTesting, EnvFileEnum::Testing);

        $this->setEnvVariables([
            'FAIL_EXPORT_JSON' => false,
        ], EnvFileEnum::Testing);
    }

    protected function setEnvVariables(array $data, EnvFileEnum ...$envFiles): void
    {
        foreach ($envFiles as $envFile) {
            foreach ($data as $key => $value) {
                $this->envVariables[$envFile->value][$key] = $value;
            }
        }
    }

    protected function generateAppKey(): string
    {
        return 'base64:' . base64_encode(random_bytes(32));
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
}
