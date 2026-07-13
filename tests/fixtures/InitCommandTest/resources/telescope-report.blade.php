_{{ \NotificationChannels\Telegram\TelegramMessage::escapeMarkdown(config('app.name')) }}_ Telescope collected entries
@foreach ($entries as $type => $count)
    @if ($count > 0)
        @php
            $displayName = $entryDisplayNameMap[$type] ?? ucfirst($type);
            $emoji = strip_tags($entryEmojiMap[$type] ?? '');
        @endphp
        {{ $emoji }} _{{ \NotificationChannels\Telegram\TelegramMessage::escapeMarkdown($displayName) }}_ ({{ $count }})
        {{ $telescopeBaseUrl }}/{{ $type }}
    @endif
@endforeach
