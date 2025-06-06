<?php

namespace RonasIT\ProjectInitializator\Enums;

use RonasIT\Support\Traits\EnumTrait;

enum AuthTypeEnum: string
{
    use EnumTrait;

    case Clerk = 'clerk';
    case None = 'none';
}