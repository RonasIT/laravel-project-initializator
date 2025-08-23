<?php

namespace RonasIT\ProjectInitializator\Enums;

use RonasIT\Support\Traits\EnumTrait;

enum AppTypeEnum: string
{
    use EnumTrait;

    case Mobile = 'Mobile';
    case Web = 'Web';
    case Multiplatform = 'Multiplatform';
}
