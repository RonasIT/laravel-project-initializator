<?php

namespace RonasIT\ProjectInitializator\Enums;

use RonasIT\Support\Traits\EnumTrait;

enum TodoCategoryEnum: string
{
    use EnumTrait;

    case Readme = 'readme';
    case Environment = 'environment';
    case Configuration = 'configuration';

    public function label(): string
    {
        return match ($this) {
            self::Readme => 'README',
            self::Environment => 'Environment',
            self::Configuration => 'Configuration',
        };
    }
}
