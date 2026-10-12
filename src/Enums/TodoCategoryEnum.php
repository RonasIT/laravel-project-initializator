<?php

namespace RonasIT\ProjectInitializator\Enums;

use RonasIT\Support\Traits\EnumTrait;

enum TodoCategoryEnum: string
{
    use EnumTrait;

    case Readme = 'README';
    case Environment = 'Environment variables';
    case Configuration = 'Configuration';
}
