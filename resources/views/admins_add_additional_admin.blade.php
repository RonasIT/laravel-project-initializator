use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class {{ $migrationName }} extends Migration
{
    public function up(): void
    {
        if (!App::environment('testing')) {
            DB::table('admins')->insert([
                'email' => '{{ $email }}',
                'password' => Hash::make('{{ $password }}'),
            ]);
        }
    }

    public function down(): void
    {
        if (!App::environment('testing')) {
            DB::table('admins')
                ->where('email', '{{ $email }}')
                ->delete();
        }
    }
}
