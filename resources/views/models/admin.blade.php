namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use RonasIT\Support\Traits\ModelTrait;

/**
 * @property int $id
 * @property string $email
 * @property string $password
 * @property string|null $remember_token
 */
class Admin extends Authenticatable
{
    use ModelTrait;

    public $timestamps = false;

    protected $fillable = [
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];
}
