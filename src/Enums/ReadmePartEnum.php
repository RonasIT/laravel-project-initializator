<?php

namespace RonasIT\ProjectInitializator\Enums;

use RonasIT\Support\Traits\EnumTrait;

enum ReadmePartEnum: string
{
    use EnumTrait;

    case ResourcesAndContacts = 'fillResourcesAndContacts';
    case Prerequisites = 'fillPrerequisites';
    case GettingStarted = 'fillGettingStarted';
    case Environments = 'fillEnvironments';
    case CredentialsAndAccess = 'fillCredentialsAndAccess';
    case ClerkAuthType = 'fillClerkAuthType';
    case Renovate = 'fillRenovate';
}
