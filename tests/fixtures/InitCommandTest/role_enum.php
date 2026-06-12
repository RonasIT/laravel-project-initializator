<?php

namespace App\Enums\User;

use RonasIT\Support\Traits\EnumTrait;

enum RoleEnum: string
{
    use EnumTrait;

    case Admin = 'admin';
    case User = 'user';
}
