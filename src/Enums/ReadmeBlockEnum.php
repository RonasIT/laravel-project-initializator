<?php

namespace RonasIT\ProjectInitializator\Enums;

use RonasIT\Support\Traits\EnumTrait;

enum ReadmeBlockEnum: string
{
    use EnumTrait;

    case ResourcesAndContacts = 'ResourcesAndContacts';
    case Prerequisites = 'Prerequisites';
    case GettingStarted = 'GettingStarted';
    case Environments = 'Environments';
    case CredentialsAndAccess = 'CredentialsAndAccess';
    case Clerk = 'Clerk';
    case Renovate = 'Renovate';
}
