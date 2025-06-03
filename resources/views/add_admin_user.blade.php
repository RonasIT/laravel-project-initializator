use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use RonasIT\Support\Traits\MigrationTrait;
use Illuminate\Support\Facades\DB;

class AddAdminUser extends Migration
{
    use MigrationTrait;

    public function up()
    {
        if (config('app.env') !== 'testing') {
            DB::table('admins')->insert([
                'name' => '{{ $name }}',
                'email' => '{{ $email }}',
                'password' => Hash::make('{{ $password }}'),
            ]);
        }
    }

    public function down()
    {
        if (config('app.env') !== 'testing') {
            DB::table('admins')
                ->where('email', '{{ $email }}')
                ->delete();
        }
    }
}