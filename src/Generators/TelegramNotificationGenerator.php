<?php

namespace RonasIT\ProjectInitializator\Generators;

use RonasIT\Larabuilder\Builders\PHPFileBuilder;
use RonasIT\ProjectInitializator\Support\FileSaver;
use RonasIT\TelescopeExtension\Contracts\ReportNotificationContract;
use RonasIT\TelescopeExtension\Notifications\TelescopeReportNotification;
use Winter\LaravelConfigWriter\ArrayFile;

class TelegramNotificationGenerator
{
    public function __construct(
        protected FileSaver $fileSaver,
    ) {
    }

    public function generate(): void
    {
        $this->addServicesConfig();
        $this->publishNotificationClass();
        $this->publishTelegramView();
        $this->bindContractInServiceProvider();
    }

    protected function addServicesConfig(): void
    {
        $config = ArrayFile::open(base_path('config/services.php'));

        $config->set('telegram.token', $config->function('env', ['TELEGRAM_BOT_TOKEN', 'YOUR BOT TOKEN HERE']));

        $config->write();
    }

    protected function publishNotificationClass(): void
    {
        $this->fileSaver->publishClass(
            template: view('initializator::telescope_report_notification'),
            fileName: 'TelescopeReportNotification',
            fileDirectory: app_path('Notifications'),
        );
    }

    protected function publishTelegramView(): void
    {
        $stubPath = __DIR__ . '/../../stubs/views/telegram/telescope-report.blade.php';

        $this->fileSaver->saveFile(
            fileName: 'telescope-report.blade.php',
            data: file_get_contents($stubPath),
            fileDirectory: resource_path('views/telegram'),
        );
    }

    protected function bindContractInServiceProvider(): void
    {
        (new PHPFileBuilder(app_path('Providers/AppServiceProvider.php')))
            ->insertCodeToMethod(
                methodName: 'register',
                code: '$this->app->bind(ReportNotificationContract::class, TelescopeReportNotification::class);',
            )
            ->addImports([
                ReportNotificationContract::class,
                TelescopeReportNotification::class,
            ])
            ->save();
    }
}
