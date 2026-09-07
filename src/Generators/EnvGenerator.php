<?php

namespace RonasIT\ProjectInitializator\Generators;

use RonasIT\ProjectInitializator\DTO\DBConnectionDTO;
use RonasIT\ProjectInitializator\Enums\AppTypeEnum;
use RonasIT\ProjectInitializator\Enums\EnvFileEnum;
use RonasIT\ProjectInitializator\Enums\StorageEnum;
use Winter\LaravelConfigWriter\EnvFile;

class EnvGenerator
{
    protected array $envVariables = [];

    public function setupEnv(string $appName, string $appUrl, DBConnectionDTO $dbConnection): void
    {
        $this->setEnvVariables([
            'APP_NAME' => $appName,
            ...$this->getDBVariables($dbConnection),
        ], ...EnvFileEnum::cases());

        $this->configureDevelopment($appUrl, $dbConnection);

        $this->configureTesting($dbConnection);
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

    public function configureTelescope(string $reportMailTo): void
    {
        $this->setEnvVariables([
            'TELESCOPE_REPORT_MAIL_TO' => $reportMailTo,
        ], EnvFileEnum::Development);
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

    protected function configureDevelopment(string $appUrl, DBConnectionDTO $dbConnection): void
    {
        $this->setEnvVariables([
            'APP_ENV' => 'development',
            'APP_URL' => $appUrl,
            'APP_MAINTENANCE_DRIVER' => 'cache',
            'APP_MAINTENANCE_STORE' => 'redis',
            'CACHE_STORE' => 'redis',
            'QUEUE_CONNECTION' => 'redis',
            'SESSION_DRIVER' => 'redis',
            'DB_CONNECTION' => $dbConnection->driver,
            'DB_HOST' => '',
            'DB_PORT' => '',
            'DB_DATABASE' => '',
            'DB_USERNAME' => '',
            'DB_PASSWORD' => '',
        ], EnvFileEnum::Development);
    }

    protected function configureTesting(DBConnectionDTO $dbConnection): void
    {
        $this->setEnvVariables([
            'APP_ENV' => 'testing',
            'APP_KEY' => $this->generateAppKey(),
            'LOG_CHANNEL' => 'stderr',
            ...$this->getDBVariables($dbConnection),
            'DB_HOST' => "{$dbConnection->host}_test",
        ], EnvFileEnum::CiTesting, EnvFileEnum::Testing);

        $this->setEnvVariables([
            'FAIL_EXPORT_JSON' => false,
        ], EnvFileEnum::Testing);
    }

    protected function getDBVariables(DBConnectionDTO $dbConnection): array
    {
        return [
            'DB_CONNECTION' => $dbConnection->driver,
            'DB_HOST' => $dbConnection->host,
            'DB_PORT' => $dbConnection->port,
            'DB_DATABASE' => $dbConnection->database,
            'DB_USERNAME' => $dbConnection->username,
            'DB_PASSWORD' => $dbConnection->password,
        ];
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
