<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use RonasIT\Support\Traits\ModelTrait;

/**
 * @property int $id
 * @property string|null $name
 * @property string|null $email
 * @property string $clerk_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class User extends Authenticatable
{
    use HasFactory;
    use ModelTrait;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'clerk_id',
    ];
}
