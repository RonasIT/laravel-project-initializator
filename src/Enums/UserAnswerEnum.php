<?php

namespace RonasIT\ProjectInitializator\Enums;

use RonasIT\Support\Traits\EnumTrait;

enum UserAnswerEnum: string
{
    use EnumTrait;

    case Later = 'later';
    case No = 'no';
}
