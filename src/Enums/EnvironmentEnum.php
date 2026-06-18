<?php

namespace RonasIT\ProjectInitializator\Enums;

use RonasIT\Support\Traits\EnumTrait;

enum EnvironmentEnum: string
{
    use EnumTrait;

    case Local = '.env';
    case Example = '.env.example';
    case Development = '.env.development';
    case CiTesting = '.env.ci-testing';
    case Testing = '.env.testing';
}
