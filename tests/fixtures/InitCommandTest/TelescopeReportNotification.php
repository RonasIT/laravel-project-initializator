<?php

namespace App\Notifications;

use Illuminate\Support\Facades\URL;
use NotificationChannels\Telegram\TelegramMessage;
use RonasIT\TelescopeExtension\Notifications\ReportNotification;

class TelescopeReportNotification extends ReportNotification
{
    public function via($notifiable): array
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable): TelegramMessage
    {
        return TelegramMessage::create()
            ->to(config('telescope.notifications.report.drivers.telegram.to'))
            ->view('telegram.telescope-report', [
                'entries' => $this->entries,
                'entryEmojiMap' => config('telescope.notifications.report.entry_emoji_map'),
                'entryDisplayNameMap' => config('telescope.notifications.report.entry_display_name_map'),
                'telescopeBaseUrl' => URL::to(config('telescope.path')),
            ])
            ->button('More details in Telescope', URL::to(config('telescope.path')));
    }
}
