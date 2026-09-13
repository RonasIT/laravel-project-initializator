<?php

namespace RonasIT\ProjectInitializator\Tests;

use RonasIT\ProjectInitializator\DTO\DBConnectionDTO;
use RonasIT\ProjectInitializator\Enums\AppTypeEnum;
use RonasIT\ProjectInitializator\Enums\StorageEnum;
use RonasIT\ProjectInitializator\Generators\EnvGenerator;
use RonasIT\ProjectInitializator\Support\TodoReporter;
use RonasIT\ProjectInitializator\Tests\Support\Traits\EnvMockTrait;
use RonasIT\ProjectInitializator\Tests\Support\Traits\TodoReporterTrait;

class EnvGeneratorTest extends TestCase
{
    use EnvMockTrait;
    use TodoReporterTrait;

    protected TodoReporter $todoReporter;

    public function setUp(): void
    {
        parent::setUp();

        $this->todoReporter = new TodoReporter();
    }

    public function testSetupEnv(): void
    {
        $this->mockNativeFunction(
            '\Winter\LaravelConfigWriter',
            $this->changeEnvFileCall('.env', 'env.example.yml', 'env.configured.yml'),
            $this->changeEnvFileCall('.env.example', 'env.example.yml', 'env.configured.yml'),
            $this->changeEnvFileCall('.env.development', 'env.example.yml', 'env.development_configured.yml'),
            $this->changeEnvFileCall('.env.ci-testing', 'env.example.yml', 'env.ci-testing_configured.yml'),
            $this->changeEnvFileCall('.env.testing', 'env.example.yml', 'env.testing_configured.yml'),
        );

        $this->mockNativeFunction(
            'RonasIT\ProjectInitializator\Generators',
            $this->copyEnvFileCall('.env'),
            $this->copyEnvFileCall('.env.development'),
            $this->copyEnvFileCall('.env.ci-testing'),
            $this->copyEnvFileCall('.env.testing'),

            $this->callRandomBytes(),
        );

        $generator = new EnvGenerator($this->todoReporter);

        $generator->setupEnv('MyApp', 'https://mysite.com', new DBConnectionDTO());

        $generator->apply();

        $this->assertEquals([
            '.env.development' => ['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'],
        ], $this->getReportedEnvVars());
    }

    public function testConfigureClerk(): void
    {
        $this->mockNativeFunction(
            '\Winter\LaravelConfigWriter',
            $this->changeEnvFileCall('.env', 'env.example.yml', 'env.clerk_configured.yml'),
            $this->changeEnvFileCall('.env.example', 'env.example.yml', 'env.clerk_configured.yml'),
            $this->changeEnvFileCall('.env.development', 'env.example.yml', 'env.development_clerk_configured.yml'),
            $this->changeEnvFileCall('.env.ci-testing', 'env.example.yml', 'env.ci-testing_configured.yml'),
            $this->changeEnvFileCall('.env.testing', 'env.example.yml', 'env.testing_configured.yml'),
        );

        $this->mockNativeFunction(
            'RonasIT\ProjectInitializator\Generators',
            $this->copyEnvFileCall('.env'),
            $this->copyEnvFileCall('.env.development'),
            $this->copyEnvFileCall('.env.ci-testing'),
            $this->copyEnvFileCall('.env.testing'),

            $this->callRandomBytes(),
        );

        $generator = new EnvGenerator($this->todoReporter);

        $generator->setupEnv('MyApp', 'https://mysite.com', new DBConnectionDTO());
        $generator->configureClerk(AppTypeEnum::Multiplatform);

        $generator->apply();

        $this->assertEquals([
            '.env' => ['CLERK_ALLOWED_ISSUER', 'CLERK_SECRET_KEY', 'CLERK_ALLOWED_ORIGINS', 'CLERK_SIGNER_KEY_PATH'],
            '.env.development' => [
                'DB_HOST',
                'DB_PORT',
                'DB_DATABASE',
                'DB_USERNAME',
                'DB_PASSWORD',
                'CLERK_ALLOWED_ISSUER',
                'CLERK_SECRET_KEY',
                'CLERK_ALLOWED_ORIGINS',
            ],
        ], $this->getReportedEnvVars());
    }

    public function testConfigureClerkMobileApp(): void
    {
        $this->mockNativeFunction(
            '\Winter\LaravelConfigWriter',
            $this->changeEnvFileCall('.env', 'env.example.yml', 'env.clerk_configured_mobile_app.yml'),
            $this->changeEnvFileCall('.env.example', 'env.example.yml', 'env.clerk_configured_mobile_app.yml'),
            $this->changeEnvFileCall('.env.development', 'env.example.yml', 'env.development_clerk_configured_mobile_app.yml'),
            $this->changeEnvFileCall('.env.ci-testing', 'env.example.yml', 'env.ci-testing_configured.yml'),
            $this->changeEnvFileCall('.env.testing', 'env.example.yml', 'env.testing_configured.yml'),
        );

        $this->mockNativeFunction(
            'RonasIT\ProjectInitializator\Generators',
            $this->copyEnvFileCall('.env'),
            $this->copyEnvFileCall('.env.development'),
            $this->copyEnvFileCall('.env.ci-testing'),
            $this->copyEnvFileCall('.env.testing'),

            $this->callRandomBytes(),
        );

        $generator = new EnvGenerator($this->todoReporter);

        $generator->setupEnv('MyApp', 'https://mysite.com', new DBConnectionDTO());
        $generator->configureClerk(AppTypeEnum::Mobile);

        $generator->apply();

        $this->assertEquals([
            '.env' => ['CLERK_ALLOWED_ISSUER', 'CLERK_SECRET_KEY', 'CLERK_SIGNER_KEY_PATH'],
            '.env.development' => [
                'DB_HOST',
                'DB_PORT',
                'DB_DATABASE',
                'DB_USERNAME',
                'DB_PASSWORD',
                'CLERK_ALLOWED_ISSUER',
                'CLERK_SECRET_KEY',
            ],
        ], $this->getReportedEnvVars());
    }

    public function testSetFilesystemDisk(): void
    {
        $this->mockNativeFunction(
            '\Winter\LaravelConfigWriter',
            $this->changeEnvFileCall('.env', 'env.example.yml', 'env.configured.yml'),
            $this->changeEnvFileCall('.env.example', 'env.example.yml', 'env.configured.yml'),
            $this->changeEnvFileCall('.env.development', 'env.example.yml', 'env.development_storage_s3.yml'),
            $this->changeEnvFileCall('.env.ci-testing', 'env.example.yml', 'env.ci-testing_configured.yml'),
            $this->changeEnvFileCall('.env.testing', 'env.example.yml', 'env.testing_configured.yml'),
        );

        $this->mockNativeFunction(
            'RonasIT\ProjectInitializator\Generators',
            $this->copyEnvFileCall('.env'),
            $this->copyEnvFileCall('.env.development'),
            $this->copyEnvFileCall('.env.ci-testing'),
            $this->copyEnvFileCall('.env.testing'),

            $this->callRandomBytes(),
        );

        $generator = new EnvGenerator($this->todoReporter);

        $generator->setupEnv('MyApp', 'https://mysite.com', new DBConnectionDTO());
        $generator->setFilesystemDisk(StorageEnum::S3);

        $generator->apply();
    }

    public function testConfigureGcsStorage(): void
    {
        $this->mockNativeFunction(
            '\Winter\LaravelConfigWriter',
            $this->changeEnvFileCall('.env', 'env.example.yml', 'env.configured.yml'),
            $this->changeEnvFileCall('.env.example', 'env.example.yml', 'env.configured.yml'),
            $this->changeEnvFileCall('.env.development', 'env.example.yml', 'env.development_storage_gcs.yml'),
            $this->changeEnvFileCall('.env.ci-testing', 'env.example.yml', 'env.ci-testing_configured.yml'),
            $this->changeEnvFileCall('.env.testing', 'env.example.yml', 'env.testing_configured.yml'),
        );

        $this->mockNativeFunction(
            'RonasIT\ProjectInitializator\Generators',
            $this->copyEnvFileCall('.env'),
            $this->copyEnvFileCall('.env.development'),
            $this->copyEnvFileCall('.env.ci-testing'),
            $this->copyEnvFileCall('.env.testing'),

            $this->callRandomBytes(),
        );

        $generator = new EnvGenerator($this->todoReporter);

        $generator->setupEnv('MyApp', 'https://mysite.com', new DBConnectionDTO());
        $generator->setFilesystemDisk(StorageEnum::GCS);
        $generator->configureGcsStorage();

        $generator->apply();

        $this->assertEquals([
            '.env.development' => [
                'DB_HOST',
                'DB_PORT',
                'DB_DATABASE',
                'DB_USERNAME',
                'DB_PASSWORD',
                'GOOGLE_CLOUD_STORAGE_BUCKET',
                'GOOGLE_CLOUD_PROJECT_ID',
            ],
        ], $this->getReportedEnvVars());
    }

    public function testApplyKeepsExistingEnvFiles(): void
    {
        $this->mockNativeFunction(
            '\Winter\LaravelConfigWriter',
            $this->changeEnvFileCall('.env', 'env.filled.yml', 'env.filled_configured.yml'),
            $this->changeEnvFileCall('.env.example', 'env.example.yml', 'env.configured.yml'),
            $this->changeEnvFileCall('.env.development', 'env.filled.yml', 'env.filled_development_configured.yml'),
            $this->changeEnvFileCall('.env.ci-testing', 'env.filled.yml', 'env.filled_ci-testing_configured.yml'),
            $this->changeEnvFileCall('.env.testing', 'env.filled.yml', 'env.filled_testing_configured.yml'),
        );

        $this->mockNativeFunction(
            'RonasIT\ProjectInitializator\Generators',
            $this->callFileExists('.env'),
            $this->callFileExists('.env.development'),
            $this->callFileExists('.env.ci-testing'),
            $this->callFileExists('.env.testing'),

            $this->callRandomBytes(),
        );

        $generator = new EnvGenerator($this->todoReporter);

        $generator->setupEnv('MyApp', 'https://mysite.com', new DBConnectionDTO());

        $generator->apply();
    }
}
