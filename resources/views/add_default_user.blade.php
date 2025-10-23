use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        if (!App::environment('testing')) {
            DB::table('users')->insert([
                'name' => '{{ $name }}',
                'email' => '{{ $email }}',
                'password' => Hash::make('{{ $password }}'),
                'role_id' => {{ $role_id }},
            ]);
        }
    }

    public function down(): void
    {
        if (!App::environment('testing')) {
            DB::table('users')
                ->where('email', '{{ $email }}')
                ->delete();
        }
    }
};
