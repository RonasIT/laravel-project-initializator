<?php

namespace RonasIT\ProjectInitializator\Enums;

use RonasIT\Support\Traits\EnumTrait;

enum AppTypeEnum: string
{
    use EnumTrait;

    case Mobile = 'mobile';
    case Web = 'web';
    case Multiplatform = 'multiplatform';
}
