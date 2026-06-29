<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use RonasIT\Support\Traits\ModelTrait;

class Admin extends Authenticatable
{
    use ModelTrait;

    protected $fillable = [
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];
}
