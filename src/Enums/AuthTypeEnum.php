<?php

namespace RonasIT\ProjectInitializator\Enums;

use RonasIT\Support\Traits\EnumTrait;

enum AuthTypeEnum: string
{
    use EnumTrait;

    case Clerk = 'clerk';
    case Jwt = 'jwt';
    case None = 'none';
}
