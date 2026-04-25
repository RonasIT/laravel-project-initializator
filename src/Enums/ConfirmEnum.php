<?php

namespace RonasIT\ProjectInitializator\Enums;

use RonasIT\Support\Traits\EnumTrait;

enum ConfirmEnum: string
{
    use EnumTrait;

    case Yes = 'yes';
    case No = 'no';
}
